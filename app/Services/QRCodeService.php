<?php

namespace App\Services;

use App\Models\Contact;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeService
{
    /**
     * Generate QR code for a contact's vCard data.
     */
    public function generateContactQR(Contact $contact): string
    {
        $vcard = $contact->toVCard();
        return $this->generateVCardQR($vcard);
    }

    /**
     * Generate QR code from vCard string.
     */
    public function generateVCardQR(string $vcard): string
    {
        return QrCode::format('svg')
            ->size(200)
            ->margin(1)
            ->generate($vcard);
    }

    /**
     * Generate QR code as base64 data URL for embedding in HTML.
     */
    public function generateContactQRDataUrl(Contact $contact): string
    {
        $vcard = $contact->toVCard();
        $svg = $this->generateVCardQR($vcard);
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}