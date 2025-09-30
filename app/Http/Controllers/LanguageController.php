<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch($locale)
    {
        // التحقق من أن اللغة مدعومة
        if (!in_array($locale, ['ar', 'en'])) {
            abort(404);
        }
        
        // حفظ اللغة في الـ session
        session(['locale' => $locale]);
        
        // تغيير اللغة للطلب الحالي
        app()->setLocale($locale);
        
        // الرجوع للصفحة السابقة
        return redirect()->back();
    }
}
