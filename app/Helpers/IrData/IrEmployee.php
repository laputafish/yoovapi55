<?php namespace App\Helpers\IrData;

class IrEmployee
{
  public $SheetNo = '000001'; // numeric(6)For submission via e-filing service 000001 to 000800, for submission by physical delivery 000001 to 099999
  public $HKID = 'AANNNNNNC'; // char(9), Employee’s HKID
  public $TypeOfForm = 'O'; // char(1); status; 'O' means “O” means Original IR56B record. Supplementary/ Replacement/ Additional of IR56B are not allowed.
  public $Surname = 'Chan'; // char(20); Employee's surname
  public $GivenName = 'TAI MAN'; // char(55);Employee's Given Names in Full
  public $NameInChinese = '歐陽大文'; // char(25); Employee’s Full Name in Chinese; UTF-8 encoding
  public $Sex = 'M'; // [M|F]; char(1) Employee's Sex
  public $MaritalStatus = 1; // [1|2]; numeric(1); Employee's Marital Status, 1 - Single/ Widowed/ Divorced/ Living Ap
  public $PpNum = 'PP1234'; // char(40); Employee’s Passport No. and Place of Issue | To be filled only if the employee does not have HKID
  public $SpouseName = 'WONG; MEI MEI'; // char(50) Spouse's Name
  public $SpouseHKID = 'AANNNNNNC'; // char(9); Spouse’s HKID with Check Digit
  public $SpousePpNum = 'PP123456'; // char(40); Spouse’s Passport No. and Place of Issue<SpouseHKID>A456789A</SpouseHKID>
  public $ResAddr = 'Flat A; 8/F; 5 Mei Lai Road'; // char(90); Employee’s Residential Address | Excluding the area; e.g. Hong Kong; Kowloon; New Territories
  public $AreaCodeResAddr = 'K'; // char(1); [H|K|N|F]; Area Code of Employee’s Residential Address | H - Hong Kong Island K - Kowloon N - New Territories F - Others
  public $PosAddr = ''; // char(60); Employee’s Postal Address
  public $Capacity = 'CLERK'; // char(40); Capacity in which Employed
  public $PtPrinEmp = ''; // char(30); If Part Time; Name of Principal Employer
  public $StartDateOfEmp = '20130401'; // numeric(8); Start Date of Employment | YYYYMMDD
  public $EndDateOfEmp = '20140331'; // numeric(8); End Date of Employment | YYYYMMDD

  public $PerOfSalary = '20130401-20140331'; // char(19); Period of Salary/Wages | YYYYMMDD-YYYYMMDD
  public $AmtOfSalary = 0; // numeric(9); Amount of Salary/Wages

  public $PerOfLeavePay = '20130401-20140331'; // char(19); Period of Leave Pay
  public $AmtOfLeavePay = 0; // numeric(9); Amount of Leave Pay

  public $PerOfDirectorFee = '20130401-20140331'; // char(19); Period of Director’s
  public $AmtOfDirectorFee = 0; // numeric(9); Amount of Director’s Fee

  public $PerOfCommFee = ''; // char(19); Period of Commission /Fees
  public $AmtOfCommFee = 0; // numeric(9); Amount of Commission /Fees

  public $PerOfBonus = '20130401 - 20140331'; //
  public $AmtOfBonus = 50000; // numeric(9)

  public $PerOfBpEtc = ''; // char(19); Period of Commission /Fees
  public $AmtOfBpEtc = 0; // numeric(9); Amount of Commission /Fees

  public $PerOfPayRetire = ''; // char(19); Period of Commission /Fees
  public $AmtOfPayRetire = 0; // numeric(9); Amount of Commission /Fees

  public $PerOfSalTaxPaid = ''; // char(19); Period of Commission /Fees
  public $AmtOfSalTaxPaid = 0; // numeric(9); Amount of Commission /Fees

  public $PerOfEduBen = ''; // char(19); Period of Commission /Fees
  public $AmtOfEduBen = 0; // numeric(9); Amount of Commission /Fees

  public $PerOfGainShareOption = ''; // char(19); Period of Commission /Fees
  public $AmtOfGainShareOption = 0; // numeric(9); Amount of Commission /Fees

  public $NatureOtherRAP1 = ''; // char(19); Period of Commission /Fees
  public $PerOfOtherRAP1 = ''; // numeric(9); Amount of Commission /Fees
  public $AmtOfOtherRAP1 = 0; // numeric(9); Amount of 1st Other Rewards; Allowances or Perquisite

  public $NatureOtherRAP2 = ''; // char(19); Period of Commission /Fees
  public $PerOfOtherRAP2 = ''; // numeric(9); Amount of Commission /Fees
  public $AmtOfOtherRAP2 = 0; // numeric(9); Amount of 2st Other Rewards; Allowances or Perquisite

  public $NatureOtherRAP3 = ''; // char(19); Period of Commission /Fees
  public $PerOfOtherRAP3 = ''; // numeric(9); Amount of Commission /Fees
  public $AmtOfOtherRAP3 = 0; // numeric(9); Amount of 2st Other Rewards; Allowances or Perquisite

  public $PerOfPension = ''; // char(10); Period of Pensions

  public $AmtOfPension = 0; // numeric(9); Amount of Pensions
  public $TotalIncome = 150000; // numeric(9); Total Income

  public $PlaceOfResInd = 0; // numeric(1) [0|1]; 0 - No Place of Residence Provided 1 - Place of Residence Provided by Employer
  public $AddrOfPlace1 = ''; // char(110); Address of 1st Place of Residence
  public $NatureOfPlace1 = ''; // char(19); Nature of 1st Place of Residence
  public $PerOfPlace1 = ''; // char(26); Period of 1st Place of Residence
  public $RentPaidEr1 = 0; // numeric(7); Rent of 1st Place of Residence Paid to Landlord by Employer
  public $RentPaidEe1 = 0; // numeric(7); Rent of 1st Place of Residence Paid to Landlord by Employee
  public $RentRefund1 = 0; //  numeric(7); Rent of 1st Place of Residence Refunded to Employee
  public $RentPaidErByEe1 = 0; //  numeric(7); Rent of 1st Place of Residence Paid to Employer by Employee

  public $AddrOfPlace2 = ''; // char(110); Address of 2nd Place of Residence
  public $NatureOfPlace2 = ''; // char(19); Nature of 2nd Place of Residence
  public $PerOfPlace2 = ''; // char(26); Period of 2nd Place of Residence
  public $RentPaidEr2 = 0; // numeric(7); Rent of 2nd Place of Residence Paid to Landlord by Employer
  public $RentPaidEe2 = 0; // numeric(7); Rent of 2nd Place of Residence Paid to Landlord by Employee
  public $RentRefund2 = 0; // numeric(7); Rent of 2nd Place of Residence Refunded to Employee
  public $RentPaidErByEe2 = 0; // numeric(7); Rent of 2nd Place of Residence Paid to Employer by Employee

  public $OverseaIncInd = 0; // numeric(1) [0;1];Non-Hong Kong; 0 - Not wholly or partly paid by a Non-Hong Kong company 1 - Yes Income Indicator
  public $AmtPaidOverseaCo = 0; // char(20); Amount Paid by Non-Hong Kong Company
  public $NameOfOverseaCo = ''; // char(60); Name of Non-Hong Kong Company
  public $AddrOfOverseaCo = ''; // char(60); Address of Non-Hong Kong Company
  public $Remarks = ''; // char(60); Remarks]

}