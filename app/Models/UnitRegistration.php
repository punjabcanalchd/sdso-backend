<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitRegistration extends Model
{
    use HasFactory;

    protected $table = 'unit_registrations';

    protected $fillable = [
        'user_id',
        'form_no',
        'form_submit',
        'statuses',
        'owner_verified',
        'owner_hash',

        'compliance_report',
        'partnership_registration',
        'permission_taken',
        'ad_interim_uid',
        'permission_number',
        'previous_document',
        'unit_name',
        'unit_type',
        'ownership_type',
        'activity_details',
        'unit_pan',
        'has_gst',
        'unit_gst',
        'unit_consent_letter_number',
        'unit_consent_letter_date',
        'unit_reg_with_bussiness',
        'unit_bussiness_portal',
        'consent_approval',
        'unit_extracting_ground_water',
        'unit_extracting_water_date',
        'unit_extraction_water_date',
        'unit_consent_letter_link',
        'other_unit_type',
        'other_ownership_type',
        'water_type',
        'unit_contains_sports_ground',
        'is_sugar_mill',

        'owner_name',
        'owner_designation',
        'owner_head_off_add',
        'owner_mobile',
        'owner_email',
        'owner_std_code',
        'owner_landline',
        'multiple_owner',
        'owner_mobile_verified',
        'owner_mobile_otp',
        'id_proof',
        'id_proof_number',

        'unit_district',
        'assessment_block',
        'unit_located_area',
        'unit_village',
        'unit_subdist',
        'unit_street_add',
        'unit_land_mark',
        'unit_pin',
        'unit_is_in_boundary',
        'unit_corp_comm',

        'unit_pan_card',
        'unit_gst_ceritificate',
        'unit_land_doc',

        'unit_latitude',
        'unit_longitude',
        'legacy_unit_id',
        'ip_app_id',
    ];
}