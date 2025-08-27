<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'is_live' => '0',
            'close_msg' => 'Down for maintenance',
            'site_url' => 'http://localhost/ENCOMPOS/public',
            'site_name' => 'ENCOMPOS',
            'site_name_extended' => 'ENCOMPOS',
            'site_logo' => '/assets/images/logo/1725962670_66e019ae94ca7_default.png',
            'app_name' => 'ENCOMPOS',
            'favicon_icon' => '/assets/images/logo/1689667124_64b64634a7230_logo-icon.png',
            'favicon_icon_apple' => '/assets/images/logo/1689666983_64b645a76448f_logo-icon.png',
            'newsletter_subscribe' => '0',
            'notify_email_address' => 'noreply@encomgrid.com',
            'notify_messages_status' => '0',
            'notify_comments_status' => '0',
            'facebook_link' => 'https://www.facebook.com',
            'twitter_link' => 'https://www.twitter.com',
            'instagram_link' => 'https://www.instagram.com',
            'youtube_link' => 'https://www.youtube.com',
            'linkedin_link' => 'https://www.linkedin.com',
            'whatsapp_link' => 'https://www.whatsapp.com',
            'meta_keywords' => 'ENCOMPOS, POS System, Retail Management, Inventory Management, Sales Tracking, Customer Management',
            'meta_description' => 'ENCOMPOS Smart POS System is a comprehensive solution for managing sales, inventory, and customer relationships in retail businesses. It offers features like real-time sales tracking, inventory management, customer management, and reporting to help businesses streamline their operations and improve efficiency.',
            'meta_title' => 'ENCOMPOS - Smart POS System for Retail Management',
            'meta_image' => 'assets/uploads/seo/2023/03/28/202303288343.png',
            'contact_address' => 'RCRC Street, Kustia, Bangladesh',
            'contact_phone' => '+880 000-0000000',
            'contact_email' => 'info@encomgrid.com',
            'working_hour' => 'Sun - Thu 10:30am - 07:00pm',
            'custom_css' => '',
            'note_to_customer_invoice' => 'Thank You For Shopping. Please Come Again.',
            'is_show_logo_invoice' => '0',
            'is_show_site_invoice' => '1',
            'is_show_phone_invoice' => '1',
            'is_show_email_invoice' => '1',
            'is_show_address_invoice' => '1',
            'is_show_customer_invoice' => '1',
            'is_show_note_invoice' => '1',
            'receiptMaxwidth' => '300px',
        ];

        foreach ($settings as $key => $value) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
