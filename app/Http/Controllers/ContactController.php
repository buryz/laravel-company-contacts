<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use App\Models\Tag;
use App\Services\ContactService;
use App\Services\ExportService;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    protected ContactService $contactService;
    protected ExportService $exportService;
    protected QRCodeService $qrCodeService;

    public function __construct(ContactService $contactService, ExportService $exportService, QRCodeService $qrCodeService)
    {
        $this->contactService = $contactService;
        $this->exportService = $exportService;
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Display a listing of contacts.
     */
    public function index(Request $request)
    {
        // Authorization check - anyone can view contacts list
        $this->authorize('viewAny', Contact::class);

        try {
            $filters = [
                'search' => $request->get('search'),
                'company' => $request->get('company'),
                'position' => $request->get('position'),
                'tags' => $request->get('tags', [])
            ];

            $contacts = $this->contactService->getPaginatedContacts($filters, 15);
            $companies = $this->contactService->getUniqueCompanies();
            $positions = $this->contactService->getUniquePositions();
            $tags = Tag::orderBy('name')->get();

            return view('contacts.index', compact('contacts', 'companies', 'positions', 'tags', 'filters'));
        } catch (\Exception $e) {
            Log::error('Błąd podczas pobierania listy kontaktów: ' . $e->getMessage());
            return back()->with('error', 'Wystąpił błąd podczas pobierania kontaktów.');
        }
    }

    /**
     * Show the form for creating a new contact.
     */
    public function create()
    {
        // Authorization check - only authenticated users can create contacts
        $this->authorize('create', Contact::class);

        try {
            $tags = Tag::orderBy('name')->get();
            return view('contacts.create', compact('tags'));
        } catch (\Exception $e) {
            Log::error('Błąd podczas wyświetlania formularza tworzenia kontaktu: ' . $e->getMessage());
            return back()->with('error', 'Wystąpił błąd podczas ładowania formularza.');
        }
    }

    /**
     * Store a newly created contact in storage.
     */
    public function store(StoreContactRequest $request)
    {
        // Authorization check - only authenticated users can create contacts
        $this->authorize('create', Contact::class);

        try {
            $contact = $this->contactService->createContact(
                $request->validated(),
                auth()->user()
            );

            return redirect()->route('contacts.index')
                           ->with('success', 'Kontakt został dodany pomyślnie.');
        } catch (\Exception $e) {
            Log::error('Błąd podczas tworzenia kontaktu: ' . $e->getMessage());
            return back()->withInput()
                        ->with('error', 'Wystąpił błąd podczas dodawania kontaktu.');
        }
    }

    /**
     * Display the specified contact.
     */
    public function show(Contact $contact)
    {
        try {
            $contact->load(['tags', 'creator']);
            
            // If this is an AJAX request, return JSON with QR code
            if (request()->ajax()) {
                return response()->json([
                    'contact' => $contact,
                    'vcard' => $this->contactService->generateVCard($contact),
                    'qr_code' => $this->qrCodeService->generateContactQRDataUrl($contact)
                ]);
            }
            
            return view('contacts.show', compact('contact'));
        } catch (\Exception $e) {
            Log::error('Błąd podczas wyświetlania kontaktu: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json(['error' => 'Wystąpił błąd podczas pobierania kontaktu.'], 500);
            }
            
            return back()->with('error', 'Wystąpił błąd podczas wyświetlania kontaktu.');
        }
    }

    /**
     * Show the form for editing the specified contact.
     */
    public function edit(Contact $contact)
    {
        // Authorization check - only authenticated users can edit contacts
        $this->authorize('update', $contact);

        try {
            $contact->load(['tags']);
            $tags = Tag::orderBy('name')->get();
            
            return view('contacts.edit', compact('contact', 'tags'));
        } catch (\Exception $e) {
            Log::error('Błąd podczas wyświetlania formularza edycji kontaktu: ' . $e->getMessage());
            return back()->with('error', 'Wystąpił błąd podczas ładowania formularza edycji.');
        }
    }

    /**
     * Update the specified contact in storage.
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        // Authorization check - only authenticated users can update contacts
        $this->authorize('update', $contact);

        try {
            $this->contactService->updateContact($contact, $request->validated());

            return redirect()->route('contacts.index')
                           ->with('success', 'Kontakt został zaktualizowany pomyślnie.');
        } catch (\Exception $e) {
            Log::error('Błąd podczas aktualizacji kontaktu: ' . $e->getMessage());
            return back()->withInput()
                        ->with('error', 'Wystąpił błąd podczas aktualizacji kontaktu.');
        }
    }

    /**
     * Remove the specified contact from storage.
     */
    public function destroy(Contact $contact)
    {
        // Authorization check - only authenticated users can delete contacts
        $this->authorize('delete', $contact);

        try {
            $contactName = $contact->full_name;
            $this->contactService->deleteContact($contact);

            return redirect()->route('contacts.index')
                           ->with('success', "Kontakt {$contactName} został usunięty pomyślnie.");
        } catch (\Exception $e) {
            Log::error('Błąd podczas usuwania kontaktu: ' . $e->getMessage());
            return back()->with('error', 'Wystąpił błąd podczas usuwania kontaktu.');
        }
    }

    /**
     * Export contacts to CSV.
     */
    public function export(Request $request)
    {
        // Authorization check - only authenticated users can export contacts
        $this->authorize('export', Contact::class);

        try {
            $filters = [
                'search' => $request->get('search'),
                'company' => $request->get('company'),
                'position' => $request->get('position'),
                'tags' => $request->get('tags', [])
            ];

            $csv = $this->exportService->exportContactsToCSV($filters);
            $filename = $this->exportService->generateCSVFilename();

            return response($csv)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
                ->header('Content-Length', strlen($csv));
        } catch (\Exception $e) {
            Log::error('Błąd podczas eksportu kontaktów: ' . $e->getMessage());
            return back()->with('error', 'Wystąpił błąd podczas eksportu kontaktów.');
        }
    }

    /**
     * Generate QR code for contact vCard.
     */
    public function generateQR(Contact $contact)
    {
        // Authorization check - all users can generate QR codes
        $this->authorize('generateQR', $contact);

        try {
            $qrCode = $this->qrCodeService->generateContactQR($contact);
            
            if (request()->ajax()) {
                return response()->json([
                    'qr_code' => $this->qrCodeService->generateContactQRDataUrl($contact),
                    'contact' => $contact->load(['tags']),
                    'vcard' => $this->contactService->generateVCard($contact)
                ]);
            }
            
            return response($qrCode)
                ->header('Content-Type', 'image/svg+xml');
        } catch (\Exception $e) {
            Log::error('Błąd podczas generowania kodu QR: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json(['error' => 'Wystąpił błąd podczas generowania kodu QR.'], 500);
            }
            
            return back()->with('error', 'Wystąpił błąd podczas generowania kodu QR.');
        }
    }
}