<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles
        $roles = [
            'SuperAdmin',
            'Admin',
            'cashier',
            'sales_associate',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // All permissions
        $allPermissions = [
            'dashboard_view',
            // Customer
            'customer_create','customer_view','customer_update','customer_delete','customer_sales',
            // Supplier
            'supplier_view','supplier_create','supplier_update','supplier_delete',
            // Product
            'product_create','product_view','product_update','product_delete','product_import',
            // Brand
            'brand_create','brand_view','brand_update','brand_delete',
            // Category
            'category_create','category_view','category_update','category_delete',
            // Unit
            'unit_create','unit_view','unit_update','unit_delete',
            // Sale
            'sale_create','sale_view','sale_update','sale_delete',
            // Purchase
            'purchase_create','purchase_view','purchase_update','purchase_delete',
            // Reports
            'reports_summary','reports_sales','reports_inventory',
            // Currency
            'currency_create','currency_view','currency_update','currency_delete','currency_set_default',
            // Role & Permission
            'role_create','role_view','role_update','role_delete','permission_view',
            // User
            'user_create','user_view','user_update','user_delete','user_suspend',
            // Settings
            'website_settings','contact_settings','socials_settings','style_settings',
            'custom_settings','notification_settings','website_status_settings','invoice_settings',
        ];

        // Admin permissions subset
        $adminPermissions = [
            'dashboard_view',
            // Customer
            'customer_create','customer_view','customer_update','customer_delete','customer_sales',
            // Supplier
            'supplier_view','supplier_create','supplier_update','supplier_delete',
            // Product
            'product_create','product_view','product_update','product_delete','product_import',
            // Brand
            'brand_create','brand_view','brand_update','brand_delete',
            // Category
            'category_create','category_view','category_update','category_delete',
            // Unit
            'unit_create','unit_view','unit_update','unit_delete',
            // Sale
            'sale_create','sale_view','sale_update','sale_delete',
            // Purchase
            'purchase_create','purchase_view','purchase_update',
            // Reports
            'reports_summary','reports_sales','reports_inventory',
        ];

        // Cashier permissions
        $cashierPermissions = [
            'sale_create',
            'sale_view',
            'customer_view',
            'product_create',
            'product_view',
            'product_update',
            'product_delete',
            'product_import',
            'purchase_create',
        ];

        // Sales Associate permissions
        $salesPermissions = [
            'sale_create',
            'sale_view',
            'sale_update',
        ];

        // Create all permissions in DB
        foreach ($allPermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Assign permissions to roles
        Role::where('name', 'SuperAdmin')->first()->syncPermissions($allPermissions);
        Role::where('name', 'Admin')->first()->syncPermissions($adminPermissions);
        Role::where('name', 'cashier')->first()->syncPermissions($cashierPermissions);
        Role::where('name', 'sales_associate')->first()->syncPermissions($salesPermissions);
    }
}
