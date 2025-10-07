<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dashboard Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the dashboard for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'sidebar' => [
        'airlines' => 'شركات الطيران',
        'airports' => 'المطارات',
        'airport_routes' => 'مسارات المطارات',
        'branches' => 'الفروع',
        'classifications' => 'التصنيفات',
        'clients' => 'العملاء',
        'currencies' => 'العملات',
        'franchises' => 'الامتيازات',
        'lead_sources' => 'مصادر العملاء المحتملين',
        'passengers' => 'المسافرون',
        'suppliers' => 'الموردون',
        'tax_types' => 'أنواع الضرائب',
        'ticket_passengers' => 'مسافرو التذاكر',
        'ticket_segments' => 'شرائح التذاكر',
        'tickets' => 'التذاكر',
        'upload_tickets' => 'رفع تذاكر',
        'account_statement' => 'كشف الحساب',
        'free_invoices' => 'الفواتير الحرة',
    ],

    'upload_ticket' => [
        'form' => [
            'file_label' => 'رفع ملف TXT',
            'file_required' => 'هذا الحقل مطلوب',
        ],
        'messages' => [
            'select_file' => 'من فضلك اختر ملفاً',
            'zip_open_error' => 'تعذّر فتح ملف ZIP',
            'file_not_found' => 'الملف غير موجود على القرص',
            'import_success' => 'تم استيراد التذكرة/التذاكر بنجاح',
            'import_error' => 'حدث خطأ أثناء الاستيراد',
        ],
    ],

    'save' => 'حفظ',


    /*
    |--------------------------------------------------------------------------
    | Common Form Fields
    |--------------------------------------------------------------------------
    |
    | These are commonly used field labels that appear in multiple resources.
    | Keep them here for consistency and easy maintenance.
    |
    */

    'fields' => [
        'name' => 'الاسم',
        'name_ar' => 'الاسم بالعربي',
        'name_en' => 'الاسم بالإنجليزية',
        'code' => 'الكود',
        'code_string' => 'نص الكود',
        'is_internal' => 'داخلي',
        // Airline specific fields
        'iata_code' => 'كود IATA',
        'iata_prefix' => 'بادئة IATA',
        'icao_code' => 'كود ICAO',
        'created_date' => 'تاريخ الإنشاء',
        'last_modified_date' => 'تاريخ آخر تعديل',
        'status' => 'الحالة',
        'description' => 'الوصف',
        'phone' => 'الهاتف',
        'email' => 'البريد الإلكتروني',
        'address' => 'العنوان',
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        // Client specific fields
        'company_name' => 'اسم الشركة',
        'tax_number' => 'الرقم الضريبي',
        'sales_rep' => 'مندوب المبيعات',
        'lead_source' => 'مصدر العميل المحتمل',
        'contact_infos' => 'معلومات التواصل',
        // Currency specific fields
        'symbol' => 'الرمز',
        // Airport specific fields
        'iata' => 'كود المطار',
        'city' => 'المدينة',
        'country_code' => 'كود البلد',
        'airline_info' => 'معلومات الشركة',
        'airport_info' => 'معلومات المطار',
        // Classification specific fields
        'type' => 'النوع',
        // Branch specific fields
        'tax_number' => 'الرقم الضريبي',
        'basic_info' => 'المعلومات الأساسية',
        'contact_info' => 'معلومات التواصل',
        'add_contact_info' => 'إضافة معلومات تواصل',
        'phone' => 'الهاتف',
        // Passenger specific fields
        'passport_expiry' => 'تاريخ انتهاء جواز السفر',
        'passport_number' => 'رقم جواز السفر',
        'nationality' => 'الجنسية',
        'date_of_birth' => 'تاريخ الميلاد',
        // Tax Type specific fields
        'value' => 'القيمة',
        'classification_info' => 'معلومات التصنيف',
        'company_name_ar' => 'اسم الشركة بالعربي',
        'company_name_en' => 'اسم الشركة بالإنجليزية',
        // Passenger specific fields
        'first_name' => 'الاسم الأول',
        'last_name' => 'اسم العائلة',
        'title' => 'اللقب',
        // Ticket Passenger specific fields
        'ticket_number_core' => 'جوهر رقم التذكرة',
        // Ticket specific fields
        'gds' => 'نظام التوزيع العالمي',
        'airline_name' => 'اسم شركة الطيران',
        'validating_carrier_code' => 'كود شركة التحقق',
        'ticket_number_full' => 'رقم التذكرة الكامل',
        'ticket_number_prefix' => 'بادئة رقم التذكرة',
        'pnr' => 'رقم الحجز',
        'issue_date' => 'تاريخ الإصدار',
        'booking_date' => 'تاريخ الحجز',
        'ticket_type' => 'نوع التذكرة',
        'ticket_type_code' => 'كود نوع التذكرة',
        'trip_type' => 'نوع الرحلة',
        'is_domestic_flight' => 'رحلة داخلية',
        'itinerary_string' => 'مسار الرحلة',
        'fare_basis_out' => 'أساس السعر للذهاب',
        'fare_basis_in' => 'أساس السعر للعودة',
        'branch_code' => 'كود الفرع',
        'office_id' => 'رقم المكتب',
        'created_by_user' => 'أنشئت بواسطة المستخدم',
        'airline_id' => 'رقم شركة الطيران',
        'currency_id' => 'رقم العملة',
        'supplier_id' => 'رقم المورد',
        'sales_user_id' => 'رقم مندوب المبيعات',
        'client_id' => 'رقم العميل',
        'branch_id' => 'رقم الفرع',
        'cost_base_amount' => 'تكلفة الأساس',
        'cost_tax_amount' => 'تكلفة الضرائب',
        'cost_total_amount' => 'إجمالي التكلفة',
        'profit_amount' => 'الأرباح',
        'discount_amount' => 'قيمة الخصم',
        'extra_tax_amount' => 'مبلغ الضريبة الإضافية',
        'sale_total_amount' => 'إجمالي سعر البيع',
        'carrier_pnr_carrier' => 'شركة PNR',
        'carrier_pnr' => 'رقم PNR للشركة',
        'price_taxes_breakdown' => 'تفصيل الضرائب على السعر',
        // Ticket table specific labels
        'ticket_no' => 'رقم التذكرة',
        'booking_date_label' => 'تاريخ الحجز',
        'passenger_label' => 'المسافر',
        'branch_number' => 'رقم الفرع',
        'user_number' => 'رقم المستخدم',
        'type_code' => 'كود النوع',
        'internal' => 'داخلي',
        'cost' => 'التكلفة',
        'price_for_sale' => 'سعر البيع',
        'profits' => 'الأرباح',
        'airline_label' => 'شركة الطيران',
        'flights' => 'الرحلات',
        'type' => 'النوع',
        'franchise_name' => 'اسم الامتياز',
        'branch_name' => 'اسم الفرع',
        'client_name' => 'اسم العميل',
        'supplier_name' => 'اسم المورد',
        'sales_user_name' => 'اسم مندوب المبيعات',
        'currency_code' => 'رمز العملة',
        'ticket_data' => 'بيانات التذكرة',
        'costs_and_relations' => 'التكلفة والعلاقات',
        'price_taxes_breakdown' => 'تفصيل الضرائب على السعر',
        'code' => 'كود',
        'amount' => 'مبلغ',
        'currency' => 'العملة',
        'id'                 => 'المعرف',
        'statementable_type' => 'النوع',
        'statementable_id'   => 'الجهة',
        'date'               => 'التاريخ',
        'doc_no'             => 'رقم المستند',
        'ticket_id'          => 'التذكرة',
        'lpo_no'             => 'LPO رقم',
        'passengers'         => 'المسافرون',
        'sector'             => 'القطاع',
        'debit'              => 'مدين',
        'credit'             => 'دائن',
        'balance'            => 'الرصيد',
        'created_at'         => 'تاريخ الإنشاء',
        'updated_at'         => 'تاريخ التعديل',
        'account_statement' => 'كشف الحساب',

    ],



];
