<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained('applications')->cascadeOnDelete();
            $table->enum('document_type', [
                'protocol_review_app',//طلب مراجعة بروتوكول بحثى
                'oral_presentaion',//  العرض التقديمى الشفهى للبروتوكول البحثى  
                'pi_consent',// اقرار الباحث الرئيسى
                'research_procedures_approval', // نموذج قرار الموافقة على الاجراءات البحثية 
                'conflict_of_interest', // نموذج عدم تعارض المصالح
                'patient_consent', // نموذج الموافقة المستنيرة للمريض 
                'research_alignment_with_research_plan', // نموذج تقرير توافق الابحاث مع الخطة البحثية 
                'research_protocol' // البروتوكول البحثى
                
                /*'research', 'protocol', 'conflict_of_interest', 'irb_checklist',
                'pi_consent', 'patient_consent', 'photos_biopsies_consent', 'protocol_review_app'*/
            ]);
            $table->string('file_path', 255)->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
