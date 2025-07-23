<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to contacts table for frequently searched fields
        Schema::table('contacts', function (Blueprint $table) {
            // Check if indexes already exist before adding them
            if (!Schema::hasIndex('contacts', 'contacts_first_name_index')) {
                $table->index('first_name', 'contacts_first_name_index');
            }
            
            if (!Schema::hasIndex('contacts', 'contacts_last_name_index')) {
                $table->index('last_name', 'contacts_last_name_index');
            }
            
            if (!Schema::hasIndex('contacts', 'contacts_email_index')) {
                $table->index('email', 'contacts_email_index');
            }
            
            if (!Schema::hasIndex('contacts', 'contacts_company_index')) {
                $table->index('company', 'contacts_company_index');
            }
            
            if (!Schema::hasIndex('contacts', 'contacts_position_index')) {
                $table->index('position', 'contacts_position_index');
            }
            
            // Add a composite index for full name searches
            if (!Schema::hasIndex('contacts', 'contacts_first_last_name_index')) {
                $table->index(['first_name', 'last_name'], 'contacts_first_last_name_index');
            }
        });
        
        // Add indexes to tags table
        Schema::table('tags', function (Blueprint $table) {
            if (!Schema::hasIndex('tags', 'tags_name_index')) {
                $table->index('name', 'tags_name_index');
            }
        });
        
        // Add indexes to contact_tag pivot table
        Schema::table('contact_tag', function (Blueprint $table) {
            if (!Schema::hasIndex('contact_tag', 'contact_tag_contact_id_index')) {
                $table->index('contact_id', 'contact_tag_contact_id_index');
            }
            
            if (!Schema::hasIndex('contact_tag', 'contact_tag_tag_id_index')) {
                $table->index('tag_id', 'contact_tag_tag_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from contacts table
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('contacts_first_name_index');
            $table->dropIndex('contacts_last_name_index');
            $table->dropIndex('contacts_email_index');
            $table->dropIndex('contacts_company_index');
            $table->dropIndex('contacts_position_index');
            $table->dropIndex('contacts_first_last_name_index');
        });
        
        // Remove indexes from tags table
        Schema::table('tags', function (Blueprint $table) {
            $table->dropIndex('tags_name_index');
        });
        
        // Remove indexes from contact_tag pivot table
        Schema::table('contact_tag', function (Blueprint $table) {
            $table->dropIndex('contact_tag_contact_id_index');
            $table->dropIndex('contact_tag_tag_id_index');
        });
    }
};