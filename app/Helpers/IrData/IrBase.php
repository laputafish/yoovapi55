<?php namespace App\Helpers\IrData;

class IrBase
{
  public $section = ''; // char(3); first 3 chars of employer's file no. shown on the BIR 56A
  public $ern = ''; // char(8); last 8 char of employe's file no. numeric only
  public $yrErReturn = 0; // numeric(4); Year of Employer's Return;
  public $subDate = 20181231; // numeric(8); Submission Date; e.g. YYYYMMDD
  public $erName = 'ABC Company Ltd.'; // char(70); Employer's Name
  public $designation = 'Precendent Partner'; // char(25); Proprietor/Precendent Partner or Nature of Office Held
  public $noRecordBatch = 0; // numeric(5); No. of Records in batch; e-filing service (00001-00800); physical delivery (00001-99999)
  public $totIncomeBatch = 0; // numeric(11); total Income in batch; the total income for all employees specified in the BIRT56A
  public $employees = []; // one or more; Employee's IR56B record
}