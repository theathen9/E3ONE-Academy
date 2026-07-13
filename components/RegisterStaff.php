<?php
// ./data/register_staff.php

function register_staff($conn, $department, $idCodeStaff)
{
    $autoNameFS = "សា_" . $idCodeStaff;
    $autoNameMS = "មា_" . $idCodeStaff;
    $autoNameLS = "នា_" . $idCodeStaff;
    $autoNameEnFS = "kim_" . $idCodeStaff;
    $autoNameEnMS = "M_" . $idCodeStaff;
    $autoNameEnLS = "Na_" . $idCodeStaff;
    $student_code = sprintf("STU-%s-%02d", date("Y"), $idCodeStaff);    
    $start = strtotime("-25 years");
    $end   = strtotime("-10 years");


    $randomTimestamp = rand($start, $end);

    $dob = date("Y-m-d", $randomTimestamp);

    $prefixes = [
        '010',
        '011',
        '012',
        '015',
        '016',
        '017',
        '018',
        '060',
        '061',
        '066',
        '067',
        '068',
        '069',
        '070',
        '077',
        '078',
        '085',
        '086',
        '087',
        '088',
        '089',
        '090',
        '092',
        '093',
        '095',
        '096',
        '097',
        '098',
        '099'
    ];

    $prefix = $prefixes[array_rand($prefixes)];
    $number1 = $prefix . rand(1000000, 9999999); // 7 digits
    $number2 = $prefix . rand(1000000, 9999999); // 7 digits

    $email = "user" . rand(1000, 9999) . "@gmail.com";
?>
    <div class="card shadow border-0">

        <div class="card-body p-4 p-md-5">

            <input type="hidden" name="action" value="register_staff">


            <!-- Staff Information -->

            <h3 class="mb-4">Staff Information</h3>

            <div class="d-flex justify-content-between">
                <div class="w-75">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <input type="text" value="<?= $autoNameFS ?>" name="first_name_kh" class="form-control" placeholder="First Name Khmer" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" value="<?= $autoNameMS ?>" name="middle_name_kh" class="form-control" placeholder="Middle Name Khmer">
                        </div>
                        <div class="col-md-4">
                            <input type="text" value="<?= $autoNameLS ?>" name="last_name_kh" class="form-control" placeholder="Last Name Khmer" required>
                        </div>

                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <input type="text" value="<?= $autoNameEnFS ?>" name="first_name_en" class="form-control" placeholder="First Name English" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" value="<?= $autoNameEnMS ?>" name="middle_name_en" class="form-control" placeholder="Middle Name English">
                        </div>
                        <div class="col-md-4">
                            <input type="text" value="<?= $autoNameEnLS ?>" name="last_name_en" class="form-control" placeholder="Last Name English" required>
                        </div>
                    </div>
                </div>
                <div class="w-25 text-center">

                    <label for="staff_photo" style="cursor:pointer;">
                        <img id="staffPreviewPhoto"
                            src="../src/assets/default-user.png"
                            style="height:99px;width:99px;object-fit:cover;border-radius:6px;border:1px solid #ccc;">
                    </label>

                    <input
                        type="file"
                        name="staff_photo"
                        id="staff_photo"
                        accept="image/*"
                        style="display:none">

                    <div class="small text-muted mt-1">Click photo to upload</div>

                </div>
            </div>

            <div class="row g-3 mb-5">
                <div class="col-lg-3">
                    <input
                        required
                        type="text"
                        id="dob"
                        name="dob"
                        class="form-control"
                        placeholder="Date Of Birth"
                        class="form-control">
                </div>
                <div class="col-lg-3">
                    <select name="gender" id="gender" class="form-select" required>
                        <option value="">-- Gender --</option>
                        <option value="Male">ប្រុស</option>
                        <option value="Female">ស្រី</option>
                    </select>
                </div>

                <div class="col-lg-5">
                    <select name="department_id" id="department_id" class="form-select" required>
                        <option value="">-- Select Department --</option>

                      <?php

if (!empty($department)):
    foreach ($department as $row):
?>

    <option value="<?= $row['department_id']; ?>"
        data-code="<?= htmlspecialchars($row['department_code']); ?>">
        <?= htmlspecialchars($row['department_name']); ?>
    </option>

<?php
    endforeach;
else:
?>

    <option value="">No Department Found</option>

<?php endif; ?>

                    </select>
                </div>
                <div class="col-lg-3">
                    <input
                        required
                        type="text"
                        id="hired_at"
                        name="hired_at"
                        class="form-control"
                        placeholder="Hired At"
                        class="form-control">
                </div>

            </div>

            <!-- Address -->

            <h3 class="mb-4">Address Date Of Birth</h3>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <select id="birth_addr_province" name="birth_addr_province" class="form-select" required>
                        <option value="">-- Province --</option>

                    </select>

                </div>
                <div class="col-md-6">
                    <select id="birth_addr_district" name="birth_addr_district" class="form-select" disabled required>
                        <option value="">-- District --</option>
                        <option value="other">-- Other --</option>
                    </select>
                    <input type="text" id="other_birth_addr_district" class="form-control" placeholder="Enter village name" style="display:none;">

                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <select id="birth_addr_commune" name="birth_addr_commune" class="form-select" disabled required>
                        <option value="">-- Commune --</option>
                        <option value="other">-- Other --</option>

                    </select>
                    <input type="text" id="other_birth_addr_commune" class="form-control" placeholder="Enter village name" style="display:none;">
                </div>
                <div class="col-md-6">
                    <select id="birth_addr_village" name="birth_addr_village" class="form-select" disabled required>
                        <option value="">-- Village --</option>
                        <option value="other">-- Other --</option>
                    </select>
                    <input type="text" id="other_birth_addr_village" class="form-control" placeholder="Enter village name" style="display:none;">
                </div>
            </div>

            <h3 class="mb-4">Current Address </h3>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <select id="curr_addr_province" name="curr_addr_province" class="form-select" required>
                        <option value="">-- Province --</option>

                    </select>

                </div>
                <div class="col-md-6">
                    <select id="curr_addr_district" name="curr_addr_district" class="form-select" disabled required>
                        <option value="">-- District --</option>
                        <option value="other">-- Other --</option>
                    </select>
                    <input type="text" id="other_curr_addr_district" class="form-control" placeholder="Enter village name" style="display:none;">

                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <select id="curr_addr_commune" name="curr_addr_commune" class="form-select" disabled required>
                        <option value="">-- Commune --</option>
                        <option value="other">-- Other --</option>

                    </select>
                    <input type="text" id="other_curr_addr_commune" class="form-control" placeholder="Enter village name" style="display:none;">
                </div>
                <div class="col-md-6">
                    <select id="curr_addr_village" name="curr_addr_village" class="form-select" disabled required>
                        <option value="">-- Village --</option>
                        <option value="other">-- Other --</option>
                    </select>
                    <input type="text" id="other_curr_addr_village" class="form-control" placeholder="Enter village name" style="display:none;">
                </div>
            </div>



            <!-- Contact Information -->

            <h3 class="mb-4">Contact Information</h3>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <input type="email" value="<?= $email ?>" name="email" class="form-control" placeholder="Email Address">
                </div>
                <div class="col-md-6">
                </div>
                <div class="col-md-6">

                    <input type="tel" value="<?= $number1 ?>" name="phone1" class="form-control" placeholder="Phone Number 1">
                </div>
                <div class="col-md-6">

                    <input type="tel" name="phone2" class="form-control" placeholder="Phone Number 2">
                </div>
            </div>

            <!-- Platform Information -->

            <h3 class="mb-4">Platform Information</h3>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="tel" name="phone_number" class="form-control" placeholder="Phone Number">
                </div>
                <div class="col-md-3">

                    <input type="tel" name="account_url" class="form-control" placeholder="Account Link">
                </div>
                <div class="col-md-3">
                    <select name="platform_type" class="form-select">
                        <option value="">Select Platform</option>
                        <option value="Telegram">Telegram</option>
                    </select>

                </div>
                <!-- <div class="col-md-3">

                    <button id="btnAddPlatform" class="btn btn-primary">Add</button>
                </div> -->
            </div>

            <!-- guardian imformation -->

            <h3 hidden class="mb-4">Parent/Guardian Information</h3>

            <div hidden class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" name="guardian1_fst_name" class="form-control" placeholder="Guardian First Name">
                </div>
                <div class="col-md-4">
                    <input type="text" name="guardian1_lst_name" class="form-control" placeholder="Guardian Last Name">
                </div>
                <div class="col-md-4">
                    <select name="guardian1_relatioship_" class="form-select">
                        <option value="">-- Relationship --</option>
                        <option value="Father">Father</option>
                        <option value="Mother">Mother</option>
                    </select>
                </div>
            </div>

            <div hidden class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" name="guardian2_fst_name" class="form-control" placeholder="Guardian First Name">
                </div>
                <div class="col-md-4">
                    <input type="text" name="guardian2_lst_name" class="form-control" placeholder="Guardian Last Name">
                </div>
                <div class="col-md-4">
                    <select name="guardian2_relatioship_" class="form-select">
                        <option value="">-- Relationship --</option>
                        <option value="Father">Father</option>
                        <option value="Mother">Mother</option>
                    </select>
                </div>

            </div>
            <!-- Phone -->
            <div hidden class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" name="guardian_phone1" class="form-control" placeholder="Guardian Phone Number 1">
                </div>
                <div class="col-md-4">
                    <input type="text" name="guardian_phone2" class="form-control" placeholder="Guardian Phone Number 2">
                </div>
                <div class="col-md-4">
                    <input type="text" name="guardian_email" class="form-control" placeholder="Guardian Email">
                </div>
            </div>



            <h3 hidden class="mb-4">Parent/Guardian Current Address </h3>

            <div hidden class="row g-3 mb-3">
                <div class="col-md-6">
                    <select id="guardian_curr_addr_province" name="guardian_curr_addr_province" class="form-select">
                        <option value="">-- Province --</option>

                    </select>

                </div>
                <div class="col-md-6">
                    <select id="guardian_curr_addr_district" name="guardian_curr_addr_district" class="form-select" disabled>
                        <option value="">-- District --</option>
                        <option value="other">-- Other --</option>
                    </select>
                    <input type="text" id="other_guardian_curr_addr_district" class="form-control" placeholder="Enter village name" style="display:none;">

                </div>
            </div>

            <div hidden class="row g-3 mb-4">
                <div class="col-md-6">
                    <select id="guardian_curr_addr_commune" name="guardian_curr_addr_commune" class="form-select" disabled>
                        <option value="">-- Commune --</option>
                        <option value="other">-- Other --</option>

                    </select>
                    <input type="text" id="other_guardian_curr_addr_commune" class="form-control" placeholder="Enter village name" style="display:none;">
                </div>
                <div class="col-md-6">
                    <select id="guardian_curr_addr_village" name="guardian_curr_addr_village" class="form-select" disabled>
                        <option value="">-- Village --</option>
                        <option value="other">-- Other --</option>
                    </select>
                    <input type="text" id="other_guardian_curr_addr_village" class="form-control" placeholder="Enter village name" style="display:none;">
                </div>
            </div>


            <div class="w-100 d-flex justify-content-center mt-5">
                <div>

                    <button id="finalSubmit" type="submit" style="width: 117px;" class="btn btn-primary">
                        Register
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        const btnStaff = document.getElementById("finalSubmit");

        btnStaff.addEventListener("click", (e) => {
            e.preventDefault();

            btn.disabled = true;
            try {

                const form = document.querySelector("#staffSection form");
                form.submit();

                setTimeout(() => {
                    window.location.href = "<?= BASE_URL ?>/admin/register.php";
                }, 900);

            } catch (error) {

            }


        });
    </script>
<?php
}

?>
