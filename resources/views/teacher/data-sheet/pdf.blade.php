<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>EMIS Data Sheet</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 0; padding: 0; line-height: 1.5; }
        .page { page-break-after: always; padding: 20px; }
        .page:last-child { page-break-after: avoid; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        h1 { font-size: 13px; margin: 5px 0; }
        h2 { font-size: 11px; margin: 5px 0; text-decoration: underline; }
        h3 { font-size: 11px; font-weight: bold; margin: 10px 0 5px; }
        
        .table-border { border-collapse: collapse; width: 100%; margin-bottom: 10px; }
        .table-border th, .table-border td { border: 1px solid #000; padding: 4px; font-size: 11px; }
        
        .field-row { margin-bottom: 8px; display: table; width: 100%; }
        .field-label { display: table-cell; width: 250px; vertical-align: bottom; }
        .field-value { display: table-cell; border-bottom: 1px dotted #000; vertical-align: bottom; }
        .field-value-inline { border-bottom: 1px dotted #000; display: inline-block; padding: 0 5px; }

        .flex-row { display: table; width: 100%; margin-bottom: 8px; }
        .flex-col { display: table-cell; vertical-align: top; }

        .ol-list { list-style: none; padding-left: 20px; margin: 0; }
        .ol-list li { margin-bottom: 6px; }
        .ol-list li span.dot { display: inline-block; width: 200px; border-bottom: 1px dotted #000; margin-left: 5px; }
        
        .mt-20 { margin-top: 20px; }
        .mb-20 { margin-bottom: 20px; }
        
        /* Specific header table */
        .header-table { width: 50%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px; }
        .header-table th, .header-table td { border: 1px solid #000; padding: 4px; text-align: left; font-size: 11px; }
        .header-table th { width: 150px; }
    </style>
</head>
<body>
    <!-- PAGE 1 -->
    <div class="page">
        <div class="text-center font-bold">
            <h1 class="underline">PROVINCIAL DEPARTMENT OF EDUCATION - CENTRAL PROVINCE</h1>
            <h1 class="underline">EDUCATION MANAGEMENT INFORMATION SYSTEM (EMIS)</h1>
            <h1 class="underline">DATA SHEET</h1>
            <p class="underline" style="font-size: 10px; margin-top: 10px;">This form should be fill in English BLOCK CAPITAL letters</p>
        </div>

        <table class="header-table">
            <tr>
                <th>School Name</th>
                <td>{{ strtoupper(config('app.name')) }}</td>
            </tr>
            <tr>
                <th>School Census No</th>
                <td>{{ strtoupper($dataSheet->school_census_no) }}</td>
            </tr>
        </table>

        <h3>1. Personal Information</h3>
        <div class="field-row"><div class="field-label">1.1 NIC :</div><div class="field-value">{{ strtoupper($dataSheet->nic) }}</div></div>
        <div class="field-row"><div class="field-label">1.2 Title (Rev/Mr/Mrs/Ms):</div><div class="field-value">{{ strtoupper($dataSheet->title) }}</div></div>
        <div class="field-row"><div class="field-label">1.3 Full name :</div><div class="field-value">{{ strtoupper($dataSheet->full_name) }}</div></div>
        <div class="field-row"><div class="field-label">1.4 E – mail Address (Compulsory) :</div><div class="field-value">{{ strtoupper($dataSheet->email) }}</div></div>
        <div class="field-row"><div class="field-label">1.5 Date of Birth :</div><div class="field-value">{{ $dataSheet->date_of_birth?->format('Y-m-d') }}</div></div>
        <div class="field-row"><div class="field-label">1.6 Ethnicity:</div><div class="field-value">{{ strtoupper($dataSheet->ethnicity) }}</div></div>
        <div class="field-row"><div class="field-label">1.7 Gender :</div><div class="field-value">{{ strtoupper($dataSheet->gender) }}</div></div>
        <div class="field-row"><div class="field-label">1.9 Religion :</div><div class="field-value">{{ strtoupper($dataSheet->religion) }}</div></div>
        <div class="field-row"><div class="field-label">1.10 Mobile No :</div><div class="field-value">{{ strtoupper($dataSheet->mobile_no) }}</div></div>
        <div class="field-row"><div class="field-label">1.11 Blood Group :</div><div class="field-value">{{ strtoupper($dataSheet->blood_group) }}</div></div>

        <h3 class="mt-20">2. Permanent Residence Detail</h3>
        <div class="field-row"><div class="field-label">2.1 Address :</div><div class="field-value">{{ strtoupper($dataSheet->perm_address) }}</div></div>
        <div class="field-row"><div class="field-label">2.2 District :</div><div class="field-value">{{ strtoupper($dataSheet->perm_district) }}</div></div>
        <div class="field-row"><div class="field-label">2.3 D.S. Division :</div><div class="field-value">{{ strtoupper($dataSheet->perm_ds_division) }}</div></div>
        <div class="field-row"><div class="field-label">2.4 G.S. Division :</div><div class="field-value">{{ strtoupper($dataSheet->perm_gs_division) }}</div></div>
        <div class="field-row"><div class="field-label">2.5 Telephone :</div><div class="field-value">{{ strtoupper($dataSheet->perm_telephone) }}</div></div>
        <div class="field-row"><div class="field-label">2.6 Effective Date :</div><div class="field-value">{{ $dataSheet->perm_effective_date?->format('Y-m-d') }}</div></div>

        <h3 class="mt-20">3. Current Residence Detail</h3>
        <div class="field-row"><div class="field-label">3.1 Address :</div><div class="field-value">{{ strtoupper($dataSheet->curr_address) }}</div></div>
        <div class="field-row"><div class="field-label">3.2 District :</div><div class="field-value">{{ strtoupper($dataSheet->curr_district) }}</div></div>
        <div class="field-row"><div class="field-label">3.3 D.S. Division:</div><div class="field-value">{{ strtoupper($dataSheet->curr_ds_division) }}</div></div>
        <div class="field-row"><div class="field-label">3.4 G.S. Division :</div><div class="field-value">{{ strtoupper($dataSheet->curr_gs_division) }}</div></div>
        <div class="field-row"><div class="field-label">3.5 Telephone :</div><div class="field-value">{{ strtoupper($dataSheet->curr_telephone) }}</div></div>
        <div class="field-row"><div class="field-label">3.6 Postal Code:</div><div class="field-value">{{ strtoupper($dataSheet->curr_postal_code) }}</div></div>
        <div class="field-row"><div class="field-label">3.7 Latitude ( Not Compulsory) :</div><div class="field-value">{{ strtoupper($dataSheet->curr_latitude) }}</div></div>
        <div class="field-row"><div class="field-label">3.8 Longitude( Not Compulsory) :</div><div class="field-value">{{ strtoupper($dataSheet->curr_longitude) }}</div></div>
    </div>

    <!-- PAGE 2 -->
    <div class="page">
        <h3>4. Family Information</h3>
        <div class="field-row"><div class="field-label">4.1 Civil Status :</div><div class="field-value">
            @php $cs = strtoupper($dataSheet->civil_status); @endphp
            {{ $cs }} <span style="font-size:9px; float:right;">(Single/Married/Widowed/Other)</span>
        </div></div>
        
        <h3 style="margin-left: 20px;">4.2 Detail Of Spouse</h3>
        <div style="margin-left: 40px;">
            <div class="field-row"><div class="field-label" style="width:180px;">4.2.1. NIC :</div><div class="field-value">{{ strtoupper($dataSheet->spouse_nic) }}</div></div>
            <div class="field-row"><div class="field-label" style="width:180px;">4.2.2. Full Name :</div><div class="field-value">{{ strtoupper($dataSheet->spouse_full_name) }}</div></div>
            <div class="field-row"><div class="field-label" style="width:180px;">4.2.3. Date of Birth :</div><div class="field-value">{{ $dataSheet->spouse_date_of_birth?->format('Y-m-d') }}</div></div>
            <div class="field-row"><div class="field-label" style="width:180px;">4.2.4. Occupation :</div><div class="field-value">{{ strtoupper($dataSheet->spouse_occupation) }}</div></div>
            <div class="field-row"><div class="field-label" style="width:180px;">4.2.5. Office Address :</div><div class="field-value">{{ strtoupper($dataSheet->spouse_office_address) }}</div></div>
        </div>

        <h3 style="margin-left: 20px;">4.3 Details Of Children</h3>
        <table class="table-border" style="margin-left: 20px; width: calc(100% - 20px);">
            <tr>
                <th style="width:60%; text-align:center;">Child's Name</th>
                <th style="width:20%; text-align:center;">Date Of Birth</th>
                <th style="width:20%; text-align:center;">Gender</th>
            </tr>
            @php $childrenCount = count((array)$dataSheet->children); @endphp
            @for ($i = 0; $i < max(6, $childrenCount); $i++)
                @php $child = $dataSheet->children[$i] ?? ['name' => '', 'dob' => '', 'gender' => '']; @endphp
                <tr>
                    <td style="height: 18px;">{{ strtoupper($child['name']) }}</td>
                    <td>{{ $child['dob'] }}</td>
                    <td>{{ strtoupper($child['gender']) }}</td>
                </tr>
            @endfor
        </table>

        <h3 class="mt-20">5. Educational Qualifications</h3>
        <div class="flex-row">
            <div class="flex-col" style="width: 50%;">
                <div class="font-bold">G.C.L. (O/L)</div>
                <div style="margin-top: 5px;">Year <span class="field-value-inline" style="width:100px;">{{ strtoupper($dataSheet->gceo_l_1_year) }}</span></div>
                <div class="font-bold" style="margin-top: 5px;">Subjects</div>
                <ul class="ol-list">
                    @for($i = 0; $i < 10; $i++)
                        <li>{{ $i + 1 }}. <span class="dot">{{ strtoupper($dataSheet->gceo_l_1_subjects[$i] ?? '') }}</span></li>
                    @endfor
                </ul>
            </div>
            <div class="flex-col" style="width: 50%;">
                <div class="font-bold">G.C.L. (O/L)</div>
                <div style="margin-top: 5px;">Year <span class="field-value-inline" style="width:100px;">{{ strtoupper($dataSheet->gceo_l_2_year) }}</span></div>
                <div class="font-bold" style="margin-top: 5px;">Subjects</div>
                <ul class="ol-list">
                    @for($i = 0; $i < 10; $i++)
                        <li>{{ $i + 1 }}. <span class="dot">{{ strtoupper($dataSheet->gceo_l_2_subjects[$i] ?? '') }}</span></li>
                    @endfor
                </ul>
            </div>
        </div>

        <div class="mt-20">
            <div class="font-bold">G.C.E(A/L)</div>
            <div style="margin-top: 5px;">Year : <span class="field-value-inline" style="width:200px;">{{ strtoupper($dataSheet->gcea_l_year) }}</span></div>
            <div class="font-bold" style="margin-top: 5px;">Subjects</div>
            <ul class="ol-list">
                @for($i = 0; $i < 4; $i++)
                    <li>{{ sprintf('%02d', $i + 1) }}.<span class="dot" style="width: 250px;">{{ strtoupper($dataSheet->gcea_l_subjects[$i] ?? '') }}</span></li>
                @endfor
            </ul>
        </div>
    </div>

    <!-- PAGE 3 -->
    <div class="page">
        <table class="table-border mt-20">
            <tr>
                <th style="width: 50%; text-align:center;">Professional Qualifications</th>
                <th style="width: 30%; text-align:center;">Subjects<br>(Degree)</th>
                <th style="width: 20%; text-align:center;">Effective Date</th>
            </tr>
            @php 
                $proQual = (array)$dataSheet->professional_qualifications; 
                $standardQuals = ['National dip in Teaching', 'Teacher Training', 'Diploma', 'Degree', 'Post Graduate diploma in Education', 'Masters', 'B.ed.', 'M.ed'];
                $filledQuals = collect($proQual)->keyBy('qual')->map(function($q) { return [
                    'subjects_degree' => $q['subjects_degree'], 
                    'effective_date' => $q['effective_date']
                ];})->toArray();
            @endphp
            @foreach($standardQuals as $q)
                @php $fq = $filledQuals[$q] ?? null; @endphp
                <tr>
                    <td style="height: 18px;">
                        @if(in_array($q, ['National dip in Teaching', 'Teacher Training']))
                            {{ $q }} (.............................................)
                        @else
                            {{ $q }}
                        @endif
                    </td>
                    <td>{{ $fq ? strtoupper($fq['subjects_degree']) : '' }}</td>
                    <td>{{ $fq ? $fq['effective_date'] : '' }}</td>
                </tr>
            @endforeach
            @foreach($proQual as $q)
                @if(!in_array($q['qual'], $standardQuals) && $q['qual'] != '')
                    <tr>
                        <td style="height: 18px;">{{ strtoupper($q['qual']) }}</td>
                        <td>{{ strtoupper($q['subjects_degree']) }}</td>
                        <td>{{ $q['effective_date'] }}</td>
                    </tr>
                @endif
            @endforeach
        </table>

        <h3 class="mt-20">6. First Appointment Date Details</h3>
        <div style="margin-left: 20px;">
            <div class="field-row"><div class="field-label">6.1. Appointment Date :</div><div class="field-value">{{ $dataSheet->appt_date?->format('Y-m-d') }}</div></div>
            <div class="field-row"><div class="field-label">6.2. Date Of Attendant to school :</div><div class="field-value">{{ $dataSheet->appt_attendant_date?->format('Y-m-d') }}</div></div>
            <div class="field-row"><div class="field-label">6.3. Appointment Letter No and Date :</div><div class="field-value">{{ strtoupper($dataSheet->appt_letter_no_date) }}</div></div>
            <div class="field-row"><div class="field-label">6.4. Designation :</div><div class="field-value">{{ strtoupper($dataSheet->appt_designation) }}</div></div>
            <div class="field-row"><div class="field-label">6.5. Grade :</div><div class="field-value">{{ strtoupper($dataSheet->appt_grade) }}</div></div>
            <div class="field-row"><div class="field-label">6.6. Province :</div><div class="field-value">{{ strtoupper($dataSheet->appt_province) }}</div></div>
            <div class="field-row"><div class="field-label">6.7. District :</div><div class="field-value">{{ strtoupper($dataSheet->appt_district) }}</div></div>
            <div class="field-row"><div class="field-label">6.8. Zonal Education Office :</div><div class="field-value">{{ strtoupper($dataSheet->appt_zonal_office) }}</div></div>
            <div class="field-row"><div class="field-label">6.9. School :</div><div class="field-value">{{ strtoupper($dataSheet->appt_school) }}</div></div>
            <div class="field-row"><div class="field-label">6.10. Subject :</div><div class="field-value">{{ strtoupper($dataSheet->appt_subject) }}</div></div>
        </div>

        <h3 class="mt-20">7. Service History</h3>
        <table class="table-border" style="margin-left: 20px; width: calc(100% - 20px);">
            <tr>
                <th style="width: 25%; text-align:center;">Zone</th>
                <th style="width: 35%; text-align:center;">School</th>
                <th style="width: 20%; text-align:center;">Period</th>
                <th style="width: 20%; text-align:center;">Grade</th>
            </tr>
            @php $serviceCount = count((array)$dataSheet->service_history); @endphp
            @for ($i = 0; $i < max(8, $serviceCount); $i++)
                @php $srv = $dataSheet->service_history[$i] ?? ['zone' => '', 'school' => '', 'period' => '', 'grade' => '']; @endphp
                <tr>
                    <td style="height: 18px;">{{ strtoupper($srv['zone']) }}</td>
                    <td>{{ strtoupper($srv['school']) }}</td>
                    <td>{{ strtoupper($srv['period']) }}</td>
                    <td>{{ strtoupper($srv['grade']) }}</td>
                </tr>
            @endfor
        </table>
    </div>

    <!-- PAGE 4 -->
    <div class="page">
        <h3>8. Currently Teaching Subjects(Teacher/Principal)</h3>
        <div style="margin-left: 20px;">
            <div class="field-row"><div class="field-label">8.1 Appointment Date :</div><div class="field-value">{{ $dataSheet->ctch_appt_date?->format('Y-m-d') }}</div></div>
            <div class="field-row"><div class="field-label">8.2. Date Of attended to school :</div><div class="field-value">{{ $dataSheet->ctch_attended_date?->format('Y-m-d') }}</div></div>
            <div class="field-row"><div class="field-label">8.3. Appointment Letter No and Date :</div><div class="field-value">{{ strtoupper($dataSheet->ctch_appt_letter_no_date) }}</div></div>
            <div class="field-row"><div class="field-label">8.4. Designation :</div><div class="field-value">{{ strtoupper($dataSheet->ctch_designation) }}</div></div>
            <div class="field-row"><div class="field-label">8.5. Grade :</div><div class="field-value">{{ strtoupper($dataSheet->ctch_grade) }}</div></div>
            <div class="field-row"><div class="field-label">8.6. Province :</div><div class="field-value">{{ strtoupper($dataSheet->ctch_province) }}</div></div>
            <div class="field-row"><div class="field-label">8.7. District :</div><div class="field-value">{{ strtoupper($dataSheet->ctch_district) }}</div></div>
            <div class="field-row"><div class="field-label">8.8. Zonal Education Office :</div><div class="field-value">{{ strtoupper($dataSheet->ctch_zonal_office) }}</div></div>
            <div class="field-row"><div class="field-label">8.9. School :</div><div class="field-value">{{ strtoupper($dataSheet->ctch_school) }}</div></div>
            <div class="field-row"><div class="field-label">8.10. Subject :</div><div class="field-value">{{ strtoupper($dataSheet->ctch_subject) }}</div></div>
            
            <div style="margin-top: 15px;">
                <div>8.11. If you are teaching another subject or subjects without teaching the subject that you are appointed, mention that subject or subjects :</div>
                <div style="border-bottom: 1px dotted #000; min-height: 20px; margin-top: 5px;">{{ strtoupper($dataSheet->ctch_teaching_without_appt) }}</div>
            </div>

            <div style="margin-top: 15px;">
                <div>8.12. If you are teaching another subject or subjects in addition to the subject that you are appointed, mention that subject or subjects.</div>
                <div style="border-bottom: 1px dotted #000; min-height: 20px; margin-top: 5px;">{{ strtoupper($dataSheet->ctch_teaching_in_addition) }}</div>
            </div>
            
            <h3 class="mt-20">8.13. Capability of Teaching Other Subjects</h3>
            <table class="table-border">
                <tr>
                    <th style="width: 50%; text-align:center;">Capability Of Teaching Subjects</th>
                    <th style="width: 25%; text-align:center;">Medium</th>
                    <th style="width: 25%; text-align:center;">Section/Grades</th>
                </tr>
                @php $capCount = count((array)$dataSheet->capability_subjects); @endphp
                @for ($i = 0; $i < max(4, $capCount); $i++)
                    @php $cap = $dataSheet->capability_subjects[$i] ?? ['subject' => '', 'medium' => '', 'section_grades' => '']; @endphp
                    <tr>
                        <td style="height: 18px;">{{ strtoupper($cap['subject']) }}</td>
                        <td>{{ strtoupper($cap['medium']) }}</td>
                        <td>{{ strtoupper($cap['section_grades']) }}</td>
                    </tr>
                @endfor
            </table>
        </div>

        <div style="margin-top: 40px; line-height: 2;">
            <p>Certify that the above information is true and correct.</p>
            <div class="flex-row" style="margin-top: 20px;">
                <div class="flex-col" style="width: 50%;">
                    Date - ....................................................
                </div>
                <div class="flex-col" style="width: 50%;">
                    Signature - ....................................................
                </div>
            </div>

            <p style="margin-top: 40px;">Certify that the above information is true and correct.</p>
            <div class="flex-row" style="margin-top: 20px;">
                <div class="flex-col" style="width: 50%;">
                    Date - ....................................................
                </div>
                <div class="flex-col" style="width: 50%;">
                    Principal Signature -....................................................
                </div>
            </div>
        </div>
    </div>
</body>
</html>
