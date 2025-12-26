<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vista 1: Reporte de Ventas por Pedido
        DB::statement("
            CREATE OR REPLACE VIEW v_sales_by_order AS
            SELECT 
                s.id as sale_id,
                s.invoice_number,
                s.date as sale_date,
                s.status,
                s.source,
                c.id as client_id,
                c.name as client_name,
                c.document as client_document,
                c.email as client_email,
                c.phone1 as client_phone,
                si.id as sale_item_id,
                si.product_id,
                p.name as product_name,
                p.sku as product_sku,
                si.quantity,
                si.price,
                (si.quantity * si.price) as subtotal,
                s.tax,
                s.total as sale_total,
                l.name as location_name
            FROM sales s
            INNER JOIN clients c ON s.client_id = c.id
            INNER JOIN sale_items si ON s.id = si.sale_id
            INNER JOIN products p ON si.product_id = p.id
            LEFT JOIN locations l ON s.location_id = l.id
        ");

        // Vista 2: Reporte de Ventas por Cliente
        DB::statement("
            CREATE OR REPLACE VIEW v_sales_by_client AS
            SELECT 
                c.id as client_id,
                c.name as client_name,
                c.document,
                c.email,
                c.phone1,
                c.address,
                COUNT(DISTINCT s.id) as total_orders,
                SUM(s.total) as total_purchased,
                AVG(s.total) as avg_order_value,
                MAX(s.date) as last_purchase_date,
                MIN(s.date) as first_purchase_date,
                DATEDIFF(NOW(), MAX(s.date)) as days_since_last_purchase
            FROM clients c
            INNER JOIN sales s ON c.id = s.client_id
            GROUP BY c.id, c.name, c.document, c.email, c.phone1, c.address
        ");

        // Vista 3: Reporte de Ventas por Producto x Cliente
        DB::statement("
            CREATE OR REPLACE VIEW v_sales_by_product_client AS
            SELECT 
                c.id as client_id,
                c.name as client_name,
                c.document as client_document,
                p.id as product_id,
                p.name as product_name,
                p.sku,
                COUNT(DISTINCT s.id) as times_purchased,
                SUM(si.quantity) as total_quantity,
                SUM(si.quantity * si.price) as total_amount,
                AVG(si.price) as avg_price,
                MIN(si.price) as min_price,
                MAX(si.price) as max_price,
                MAX(s.date) as last_purchase_date,
                MIN(s.date) as first_purchase_date
            FROM clients c
            INNER JOIN sales s ON c.id = s.client_id
            INNER JOIN sale_items si ON s.id = si.sale_id
            INNER JOIN products p ON si.product_id = p.id
            GROUP BY c.id, c.name, c.document, p.id, p.name, p.sku
        ");

        // Vista 4: Reporte de Ventas por Período (con análisis temporal)
        DB::statement("
            CREATE OR REPLACE VIEW v_sales_by_period AS
            SELECT 
                s.id as sale_id,
                s.invoice_number,
                s.date as sale_date,
                s.status,
                DATE(s.date) as sale_day,
                YEAR(s.date) as sale_year,
                MONTH(s.date) as sale_month,
                DAY(s.date) as sale_day_of_month,
                DAYNAME(s.date) as day_name,
                WEEK(s.date) as sale_week,
                QUARTER(s.date) as sale_quarter,
                c.id as client_id,
                c.name as client_name,
                c.document as client_document,
                p.id as product_id,
                p.name as product_name,
                p.sku as product_sku,
                si.quantity,
                si.price,
                (si.quantity * si.price) as subtotal,
                s.tax,
                s.total as sale_total,
                s.source,
                l.name as location_name
            FROM sales s
            INNER JOIN clients c ON s.client_id = c.id
            INNER JOIN sale_items si ON s.id = si.sale_id
            INNER JOIN products p ON si.product_id = p.id
            LEFT JOIN locations l ON s.location_id = l.id
            
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_sales_by_order');
        DB::statement('DROP VIEW IF EXISTS v_sales_by_client');
        DB::statement('DROP VIEW IF EXISTS v_sales_by_product_client');
        DB::statement('DROP VIEW IF EXISTS v_sales_by_period');
    }
};
