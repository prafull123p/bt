<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Admission Form</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .admission-form {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: #0d6efd;
            font-weight: 700;
        }

        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="admission-form">
                    <div class="form-header">
                        <h2>College Admission Form</h2>
                        <p>Please fill out all required fields carefully</p>
                    </div>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">
                            Admission form submitted successfully! We'll contact you soon.
                        </div>
                    <?php elseif (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            There was an error submitting your form. Please try again.
                        </div>
                    <?php endif; ?>

                    <form action="process_admission.php" method="POST" enctype="multipart/form-data">
                        <!-- Personal Information -->
                        <fieldset class="mb-4">
                            <legend class="border-bottom pb-2">Personal Information</legend>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label required-field">First Name</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label required-field">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dob" class="form-label required-field">Date of Birth</label>
                                    <input type="date" class="form-control" id="dob" name="dob" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label required-field">Gender</label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="" selected disabled>Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label required-field">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label required-field">City</label>
                                    <input type="text" class="form-control" id="city" name="city" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="state" class="form-label required-field">State</label>
                                    <input type="text" class="form-control" id="state" name="state" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="pincode" class="form-label required-field">Pincode</label>
                                    <input type="text" class="form-control" id="pincode" name="pincode" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label required-field">Country</label>
                                    <input type="text" class="form-control" id="country" name="country" value="India"
                                        required>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Contact Information -->
                        <fieldset class="mb-4">
                            <legend class="border-bottom pb-2">Contact Information</legend>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label required-field">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label required-field">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="parentPhone" class="form-label required-field">Parent/Guardian Phone
                                    Number</label>
                                <input type="tel" class="form-control" id="parentPhone" name="parentPhone" required>
                            </div>
                        </fieldset>

                        <!-- Academic Information -->
                        <fieldset class="mb-4">
                            <legend class="border-bottom pb-2">Academic Information</legend>

                            <div class="mb-3">
                                <label for="course" class="form-label required-field">Course Applying For</label>
                                <select class="form-select" id="course" name="course" required>
                                    <option value="" selected disabled>Select Course</option>
                                    <option value="B.Tech">B.Tech</option>
                                    <option value="B.Sc">B.Sc</option>
                                    <option value="B.Com">B.Com</option>
                                    <option value="BBA">BBA</option>
                                    <option value="MBA">MBA</option>
                                    <option value="M.Tech">M.Tech</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="lastQualification" class="form-label required-field">Last
                                        Qualification</label>
                                    <select class="form-select" id="lastQualification" name="lastQualification"
                                        required>
                                        <option value="" selected disabled>Select Qualification</option>
                                        <option value="10th">10th</option>
                                        <option value="12th">12th</option>
                                        <option value="Diploma">Diploma</option>
                                        <option value="Graduate">Graduate</option>
                                        <option value="Post Graduate">Post Graduate</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastSchool" class="form-label required-field">Last
                                        School/College</label>
                                    <input type="text" class="form-control" id="lastSchool" name="lastSchool" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="marks" class="form-label required-field">Percentage/GPA in Last
                                    Qualification</label>
                                <input type="text" class="form-control" id="marks" name="marks" required>
                            </div>
                        </fieldset>

                        <!-- Documents Upload -->
                        <fieldset class="mb-4">
                            <legend class="border-bottom pb-2">Documents Upload</legend>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="photo" class="form-label required-field">Passport Size Photo</label>
                                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*"
                                        required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="marksheet" class="form-label required-field">Last Qualification
                                        Marksheet</label>
                                    <input type="file" class="form-control" id="marksheet" name="marksheet"
                                        accept=".pdf,.jpg,.png" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="idProof" class="form-label required-field">ID Proof (Aadhar/Passport/Driving
                                    License)</label>
                                <input type="file" class="form-control" id="idProof" name="idProof"
                                    accept=".pdf,.jpg,.png" required>
                            </div>
                        </fieldset>

                        <!-- Declaration -->
                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="declaration" name="declaration"
                                required>
                            <label class="form-check-label" for="declaration">I hereby declare that all the information
                                provided is true and correct to the best of my knowledge.</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Submit Application</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>