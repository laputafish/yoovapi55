<?php namespace App\Models;

class SampleFormEmployee extends BaseIRDFormEmployee {
  protected $parentModelName = 'SampleForm';
  public $incrementing = false;
  public $timestamps = false;
  protected $fillable = [
    'form_id',
    'employee_id',
    'sheet_no',

    'file',
    'status',

    'hkid',
    'type_of_form',
    'surname',
    'given_name',
    'name_in_chinese',
    'sex',
    'marital_status',
    'pp_num',
    'spouse_name',
    'spouse_hkid',
    'spouse_pp_num',
    'phone_num',
    'res_addr',
    'area_code_res_addr',
    'pos_addr',
    'area_code_pos_addr',
    'capacity',
    'pt_prin_emp',

    'start_date_of_emp',
    'end_date_of_emp',

    // Income particulars
    'per_of_salary',
    'amt_of_salary',

    'per_of_leave_pay',
    'amt_leave_pay',

    'per_of_director_fee',
    'amt_director_fee',

    'per_of_comm_fee',
    'amt_of_comm_fee',

    'per_of_bonus',
    'amt_of_bonus',

    'per_of_bp_etc',
    'amt_of_bp_etc',

    'per_of_pay_retire',
    'amt_of_pay_retire',

    'per_of_sal_tax_paid',
    'amt_of_sal_tax_paid',

    'per_of_edu_ben',
    'amt_of_edu_ben',

    'per_of_gain_share_option',
    'amt_of_gain_share_option',

    'nature_other_rap1',
    'per_of_other_rap1',
    'amt_of_other_rap1',

    'nature_other_rap2',
    'per_of_other_rap2',
    'amt_of_other_rap2',

    'nature_other_rap3',
    'per_of_other_rap3',
    'amt_of_other_rap3',

    'per_of_pension',
    'amt_of_pension',

    'total_income',
    'place_of_res_ind',

    'addr_of_place1',
    'nature_of_place1',
    'per_of_place1',
    'rent_paid_er1',
    'rent_paid_ee1',
    'rent_refund1',
    'rent_paid_er_by_ee1',

    'addr_of_place2',
    'nature_of_place2',
    'per_of_place2',
    'rent_paid_er2',
    'rent_paid_er2',
    'rent_refund2',
    'rent_paid_er_by_ee2',

    'oversea_inc_ind',
    'amt_paid_oversea_co',
    'name_of_oversea_co',
    'addr_of_oversea_co',

    'amt_of_sum_withheld',

    // IR56E
    'monthly_fixed_income',
    'monthly_allowance',
    'fluctuating_income',
    'share_before_emp',

    // IR56G
    'cessation_reason',

    'remarks'
  ];
}