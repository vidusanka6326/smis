<div class="mx-auto max-w-4xl space-y-8 pb-12">
        
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">{{ __('EMIS Data Sheet :year', ['year' => $year]) }}</h1>
                <p class="text-sm text-zinc-500">{{ __('Please ensure all details are correct before submitting. This will be generated as a PDF exactly as filled.') }}</p>
            </div>
            <div class="flex gap-3">
                @if ($isSubmitted)
                    <flux:button :href="route('teacher.data-sheet.pdf')" variant="primary" icon="document-arrow-down" external>
                        {{ __('Download PDF') }}
                    </flux:button>
                @endif
            </div>
        </div>

        @if ($isSubmitted)
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ __('Submitted Successfully') }}</flux:callout.heading>
                <p>{{ __('You have successfully submitted your EMIS Data Sheet for :year. No further changes can be made.', ['year' => $year]) }}</p>
            </flux:callout>
        @else
            <form wire:submit="submitForm" class="space-y-8">
                
                {{-- Top Section --}}
                <flux:card>
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input wire:model="school_census_no" :label="__('School Census No')" placeholder="e.g. CE-12345" required />
                    </div>
                </flux:card>

                {{-- 1. Personal Information --}}
                <flux:card>
                    <flux:heading size="lg" class="mb-4">{{ __('1. Personal Information') }}</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:input wire:model="nic" :label="__('1.1 NIC')" required />
                        <flux:select wire:model="title" :label="__('1.2 Title')">
                            <flux:select.option value="">{{ __('Select Title') }}</flux:select.option>
                            <flux:select.option value="Rev">{{ __('Rev') }}</flux:select.option>
                            <flux:select.option value="Mr">{{ __('Mr') }}</flux:select.option>
                            <flux:select.option value="Mrs">{{ __('Mrs') }}</flux:select.option>
                            <flux:select.option value="Ms">{{ __('Ms') }}</flux:select.option>
                        </flux:select>
                        <flux:input wire:model="full_name" :label="__('1.3 Full Name')" class="md:col-span-2" required />
                        <flux:input wire:model="email" type="email" :label="__('1.4 E-mail Address (Compulsory)')" required />
                        <flux:input wire:model="date_of_birth" type="date" :label="__('1.5 Date of Birth')" />
                        <flux:input wire:model="ethnicity" :label="__('1.6 Ethnicity')" />
                        <flux:select wire:model="gender" :label="__('1.7 Gender')">
                            <flux:select.option value="">{{ __('Select Gender') }}</flux:select.option>
                            <flux:select.option value="Male">{{ __('Male') }}</flux:select.option>
                            <flux:select.option value="Female">{{ __('Female') }}</flux:select.option>
                        </flux:select>
                        <flux:input wire:model="religion" :label="__('1.9 Religion')" />
                        <flux:input wire:model="mobile_no" :label="__('1.10 Mobile No')" />
                        <flux:input wire:model="blood_group" :label="__('1.11 Blood Group')" />
                    </div>
                </flux:card>

                {{-- 2. Permanent Residence Detail --}}
                <flux:card>
                    <flux:heading size="lg" class="mb-4">{{ __('2. Permanent Residence Detail') }}</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:input wire:model="perm_address" :label="__('2.1 Address')" class="md:col-span-2" />
                        <flux:input wire:model="perm_district" :label="__('2.2 District')" />
                        <flux:input wire:model="perm_ds_division" :label="__('2.3 D.S. Division')" />
                        <flux:input wire:model="perm_gs_division" :label="__('2.4 G.S. Division')" />
                        <flux:input wire:model="perm_telephone" :label="__('2.5 Telephone')" />
                        <flux:input wire:model="perm_effective_date" type="date" :label="__('2.6 Effective Date')" />
                    </div>
                </flux:card>

                {{-- 3. Current Residence Detail --}}
                <flux:card>
                    <flux:heading size="lg" class="mb-4">{{ __('3. Current Residence Detail') }}</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:input wire:model="curr_address" :label="__('3.1 Address')" class="md:col-span-2" />
                        <flux:input wire:model="curr_district" :label="__('3.2 District')" />
                        <flux:input wire:model="curr_ds_division" :label="__('3.3 D.S. Division')" />
                        <flux:input wire:model="curr_gs_division" :label="__('3.4 G.S. Division')" />
                        <flux:input wire:model="curr_telephone" :label="__('3.5 Telephone')" />
                        <flux:input wire:model="curr_postal_code" :label="__('3.6 Postal Code')" />
                        <flux:input wire:model="curr_latitude" :label="__('3.7 Latitude (Not Compulsory)')" />
                        <flux:input wire:model="curr_longitude" :label="__('3.8 Longitude (Not Compulsory)')" />
                    </div>
                </flux:card>

                {{-- 4. Family Information --}}
                <flux:card>
                    <flux:heading size="lg" class="mb-4">{{ __('4. Family Information') }}</flux:heading>
                    
                    <flux:select wire:model="civil_status" :label="__('4.1 Civil Status')" class="mb-6">
                        <flux:select.option value="">{{ __('Select Status') }}</flux:select.option>
                        <flux:select.option value="Single">{{ __('Single') }}</flux:select.option>
                        <flux:select.option value="Married">{{ __('Married') }}</flux:select.option>
                        <flux:select.option value="Widowed">{{ __('Widowed') }}</flux:select.option>
                        <flux:select.option value="Other">{{ __('Other') }}</flux:select.option>
                    </flux:select>

                    <h3 class="font-medium text-sm mb-3">{{ __('4.2 Detail Of Spouse') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <flux:input wire:model="spouse_nic" :label="__('4.2.1. NIC')" />
                        <flux:input wire:model="spouse_full_name" :label="__('4.2.2. Full Name')" />
                        <flux:input wire:model="spouse_date_of_birth" type="date" :label="__('4.2.3. Date of Birth')" />
                        <flux:input wire:model="spouse_occupation" :label="__('4.2.4. Occupation')" />
                        <flux:input wire:model="spouse_office_address" :label="__('4.2.5. Office Address')" class="md:col-span-2" />
                    </div>

                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-medium text-sm">{{ __('4.3 Details Of Children') }}</h3>
                        <flux:button wire:click="addEmptyRow('children')" size="sm" variant="subtle" icon="plus">{{ __('Add Child') }}</flux:button>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($children as $index => $child)
                            <div class="flex gap-3 items-end">
                                <flux:input wire:model="children.{{ $index }}.name" :label="$index === 0 ? __('Child\'s Name') : ''" class="flex-1" />
                                <flux:input wire:model="children.{{ $index }}.dob" type="date" :label="$index === 0 ? __('Date Of Birth') : ''" class="w-40 shrink-0" />
                                <flux:select wire:model="children.{{ $index }}.gender" :label="$index === 0 ? __('Gender') : ''" class="w-32 shrink-0">
                                    <flux:select.option value="">-</flux:select.option>
                                    <flux:select.option value="Male">{{ __('Male') }}</flux:select.option>
                                    <flux:select.option value="Female">{{ __('Female') }}</flux:select.option>
                                </flux:select>
                                <flux:button wire:click="removeRow('children', {{ $index }})" variant="danger" icon="trash" class="shrink-0" />
                            </div>
                        @endforeach
                    </div>
                </flux:card>

                {{-- 5. Educational Qualifications --}}
                <flux:card>
                    <flux:heading size="lg" class="mb-4">{{ __('5. Educational Qualifications') }}</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div>
                            <flux:input wire:model="gceo_l_1_year" :label="__('G.C.L. (O/L) 1st Sitting Year')" class="mb-4" />
                            <div class="space-y-2">
                                @for($i = 0; $i < 10; $i++)
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-zinc-500 w-6">{{ $i + 1 }}.</span>
                                        <flux:input wire:model="gceo_l_1_subjects.{{ $i }}" class="flex-1" />
                                    </div>
                                @endfor
                            </div>
                        </div>
                        <div>
                            <flux:input wire:model="gceo_l_2_year" :label="__('G.C.L. (O/L) 2nd Sitting Year')" class="mb-4" />
                            <div class="space-y-2">
                                @for($i = 0; $i < 10; $i++)
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-zinc-500 w-6">{{ $i + 1 }}.</span>
                                        <flux:input wire:model="gceo_l_2_subjects.{{ $i }}" class="flex-1" />
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <div class="max-w-md mb-8 border-t pt-6">
                        <flux:input wire:model="gcea_l_year" :label="__('G.C.E(A/L) Year')" class="mb-4" />
                        <div class="space-y-2">
                            @for($i = 0; $i < 4; $i++)
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-zinc-500 w-8">{{ sprintf('%02d', $i + 1) }}.</span>
                                    <flux:input wire:model="gcea_l_subjects.{{ $i }}" class="flex-1" />
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="flex items-center justify-between mb-3 border-t pt-6">
                        <h3 class="font-medium text-sm">{{ __('Professional Qualifications') }}</h3>
                        <flux:button wire:click="addEmptyRow('professional_qualifications')" size="sm" variant="subtle" icon="plus">{{ __('Add Qualification') }}</flux:button>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($professional_qualifications as $index => $qual)
                            <div class="flex gap-3 items-end">
                                <flux:input wire:model="professional_qualifications.{{ $index }}.qual" :label="$index === 0 ? __('Professional Qualifications') : ''" placeholder="e.g. National dip in Teaching / Degree / B.ed" class="flex-1" />
                                <flux:input wire:model="professional_qualifications.{{ $index }}.subjects_degree" :label="$index === 0 ? __('Subjects (Degree)') : ''" class="flex-1" />
                                <flux:input wire:model="professional_qualifications.{{ $index }}.effective_date" type="date" :label="$index === 0 ? __('Effective Date') : ''" class="w-40 shrink-0" />
                                <flux:button wire:click="removeRow('professional_qualifications', {{ $index }})" variant="danger" icon="trash" class="shrink-0" />
                            </div>
                        @endforeach
                    </div>
                </flux:card>

                {{-- 6. First Appointment --}}
                <flux:card>
                    <flux:heading size="lg" class="mb-4">{{ __('6. First Appointment Date Details') }}</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:input wire:model="appt_date" type="date" :label="__('6.1. Appointment Date')" />
                        <flux:input wire:model="appt_attendant_date" type="date" :label="__('6.2. Date Of Attendant to school')" />
                        <flux:input wire:model="appt_letter_no_date" :label="__('6.3. Appointment Letter No and Date')" class="md:col-span-2" />
                        <flux:input wire:model="appt_designation" :label="__('6.4. Designation')" />
                        <flux:input wire:model="appt_grade" :label="__('6.5. Grade')" />
                        <flux:input wire:model="appt_province" :label="__('6.6. Province')" />
                        <flux:input wire:model="appt_district" :label="__('6.7. District')" />
                        <flux:input wire:model="appt_zonal_office" :label="__('6.8. Zonal Education Office')" />
                        <flux:input wire:model="appt_school" :label="__('6.9. School')" />
                        <flux:input wire:model="appt_subject" :label="__('6.10. Subject')" class="md:col-span-2" />
                    </div>
                </flux:card>

                {{-- 7. Service History --}}
                <flux:card>
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="lg">{{ __('7. Service History') }}</flux:heading>
                        <flux:button wire:click="addEmptyRow('service_history')" size="sm" variant="subtle" icon="plus">{{ __('Add Service') }}</flux:button>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($service_history as $index => $history)
                            <div class="flex gap-3 items-end">
                                <flux:input wire:model="service_history.{{ $index }}.zone" :label="$index === 0 ? __('Zone') : ''" class="flex-1" />
                                <flux:input wire:model="service_history.{{ $index }}.school" :label="$index === 0 ? __('School') : ''" class="flex-[1.5]" />
                                <flux:input wire:model="service_history.{{ $index }}.period" :label="$index === 0 ? __('Period') : ''" class="w-32 shrink-0" />
                                <flux:input wire:model="service_history.{{ $index }}.grade" :label="$index === 0 ? __('Grade') : ''" class="w-32 shrink-0" />
                                <flux:button wire:click="removeRow('service_history', {{ $index }})" variant="danger" icon="trash" class="shrink-0" />
                            </div>
                        @endforeach
                    </div>
                </flux:card>

                {{-- 8. Currently Teaching --}}
                <flux:card>
                    <flux:heading size="lg" class="mb-4">{{ __('8. Currently Teaching Subjects(Teacher/Principal)') }}</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <flux:input wire:model="ctch_appt_date" type="date" :label="__('8.1 Appointment Date')" />
                        <flux:input wire:model="ctch_attended_date" type="date" :label="__('8.2. Date Of attended to school')" />
                        <flux:input wire:model="ctch_appt_letter_no_date" :label="__('8.3. Appointment Letter No and Date')" class="md:col-span-2" />
                        <flux:input wire:model="ctch_designation" :label="__('8.4. Designation')" />
                        <flux:input wire:model="ctch_grade" :label="__('8.5. Grade')" />
                        <flux:input wire:model="ctch_province" :label="__('8.6. Province')" />
                        <flux:input wire:model="ctch_district" :label="__('8.7. District')" />
                        <flux:input wire:model="ctch_zonal_office" :label="__('8.8. Zonal Education Office')" />
                        <flux:input wire:model="ctch_school" :label="__('8.9. School')" />
                        <flux:input wire:model="ctch_subject" :label="__('8.10. Subject')" class="md:col-span-2" />
                    </div>

                    <flux:textarea wire:model="ctch_teaching_without_appt" :label="__('8.11. If you are teaching another subject or subjects without teaching the subject that you are appointed, mention that subject or subjects')" class="mb-6" rows="2" />
                    <flux:textarea wire:model="ctch_teaching_in_addition" :label="__('8.12. If you are teaching another subject or subjects in addition to the subject that you are appointed, mention that subject or subjects')" class="mb-8" rows="2" />

                    <div class="flex items-center justify-between mb-3 border-t pt-6">
                        <h3 class="font-medium text-sm">{{ __('8.13. Capability of Teaching Other Subjects') }}</h3>
                        <flux:button wire:click="addEmptyRow('capability_subjects')" size="sm" variant="subtle" icon="plus">{{ __('Add Capability') }}</flux:button>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($capability_subjects as $index => $cap)
                            <div class="flex gap-3 items-end">
                                <flux:input wire:model="capability_subjects.{{ $index }}.subject" :label="$index === 0 ? __('Capability Of Teaching Subjects') : ''" class="flex-1" />
                                <flux:input wire:model="capability_subjects.{{ $index }}.medium" :label="$index === 0 ? __('Medium') : ''" class="w-40 shrink-0" />
                                <flux:input wire:model="capability_subjects.{{ $index }}.section_grades" :label="$index === 0 ? __('Section/Grades') : ''" class="w-40 shrink-0" />
                                <flux:button wire:click="removeRow('capability_subjects', {{ $index }})" variant="danger" icon="trash" class="shrink-0" />
                            </div>
                        @endforeach
                    </div>
                </flux:card>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <flux:button wire:click="saveDraft" variant="ghost">{{ __('Save Draft') }}</flux:button>
                    <flux:button type="submit" variant="primary" icon="paper-airplane">{{ __('Submit Final Data Sheet') }}</flux:button>
                </div>
            </form>
        @endif
    </div>
