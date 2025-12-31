<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$db = getDB();
$id = $_GET['id'] ?? 0;

$stmt = $db->prepare("SELECT c.*, p.name as package_name FROM customers c LEFT JOIN packages p ON c.package_id = p.id WHERE c.id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    header('Location: customers.php');
    exit;
}

// Parse JSON fields
$travelCompanions = json_decode($customer['travel_companions'], true) ?: [];
$roommates = json_decode($customer['roommates'], true) ?: [];

// Extract mobile number and dialing code (stored as "+44 1234 567890")
$mobileParts = explode(' ', $customer['mobile'], 2);
$mobileCountryCode = !empty($mobileParts[0]) ? $mobileParts[0] : '';
$mobileNumber = !empty($mobileParts[1]) ? $mobileParts[1] : $customer['mobile'];

$emergencyParts = explode(' ', $customer['emergency_contact'], 2);
$emergencyCountryCode = !empty($emergencyParts[0]) ? $emergencyParts[0] : '';
$emergencyContactNumber = !empty($emergencyParts[1]) ? $emergencyParts[1] : $customer['emergency_contact'];

// Load countries JSON
$countriesJsonPath = __DIR__ . '/../template/assets/vendor/country.json';
$countries = [];
if (file_exists($countriesJsonPath)) {
    $countriesData = json_decode(file_get_contents($countriesJsonPath), true);
    if ($countriesData) {
        usort($countriesData, function($a, $b) {
            if ($a['name'] === 'United Kingdom') return -1;
            if ($b['name'] === 'United Kingdom') return 1;
            return strcmp($a['name'], $b['name']);
        });
        $countries = $countriesData;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration Form - <?php echo htmlspecialchars($customer['form_id']); ?> - Awaisi Tours</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Inter', 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            color: #2d3748; 
            line-height: 1.7; 
            padding: 0;
            min-height: 100vh;
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .container { max-width: 1400px; margin: 0 auto; padding: 40px 20px; }
        
        header { 
            text-align: center; 
            margin-bottom: 40px; 
            padding: 50px 30px; 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            color: #1a202c; 
            border-radius: 20px; 
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .logo { 
            font-size: 4rem; 
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        h1 { 
            font-size: 2.8rem; 
            margin-bottom: 10px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .subtitle { 
            font-size: 1.2rem; 
            color: #718096;
            font-weight: 500;
        }
        .form-container { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(550px, 1fr)); 
            gap: 30px; 
            margin-bottom: 40px; 
        }
        .form-section { 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px; 
            padding: 35px; 
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .form-section:hover { 
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }
        .section-title { 
            color: #2d3748; 
            border-bottom: 3px solid;
            border-image: linear-gradient(to right, #667eea, #764ba2) 1;
            padding-bottom: 15px; 
            margin-bottom: 25px; 
            font-size: 1.6rem; 
            display: flex; 
            align-items: center; 
            gap: 12px;
            font-weight: 700;
        }
        .section-title i { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.8rem;
        }
        .form-group { margin-bottom: 24px; }
        label { 
            display: block; 
            margin-bottom: 10px; 
            font-weight: 600; 
            color: #2d3748;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }
        input, select, textarea { 
            width: 100%; 
            padding: 14px 18px; 
            border: 2px solid #e2e8f0; 
            border-radius: 12px; 
            font-size: 16px; 
            transition: all 0.3s ease;
            background: #fff;
            color: #2d3748;
        }
        input:focus, select:focus, textarea:focus { 
            outline: none; 
            border-color: #667eea; 
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        .required::after { content: " *"; color: #e53e3e; font-weight: 700; }
        .form-actions { 
            display: flex; 
            justify-content: center; 
            gap: 20px; 
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e2e8f0;
        }
        button { 
            padding: 16px 40px; 
            border: none; 
            border-radius: 12px; 
            font-size: 16px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        .submit-btn { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        .submit-btn:hover { 
            transform: translateY(-4px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }
        .reset-btn { 
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            color: #4a5568;
            border: 2px solid #e2e8f0;
        }
        .reset-btn:hover { 
            background: linear-gradient(135deg, #edf2f7 0%, #e2e8f0 100%);
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        footer { 
            text-align: center; 
            margin-top: 50px; 
            padding: 30px; 
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            color: #718096; 
            font-size: 0.95rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        .instructions { 
            background: linear-gradient(135deg, #fff5e6 0%, #ffeaa7 100%);
            border: 2px solid #fdcb6e; 
            padding: 25px; 
            border-radius: 16px; 
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(253, 203, 110, 0.2);
        }
        .instructions h3 { 
            color: #d63031; 
            margin-bottom: 15px;
            font-weight: 700;
            font-size: 1.3rem;
        }
        .instructions ol { padding-left: 25px; }
        .instructions li { 
            margin-bottom: 10px;
            color: #2d3748;
            font-weight: 500;
        }
        .input-group { display: grid; gap: 15px; }
        .add-btn, .remove-btn { 
            padding: 12px 24px; 
            font-size: 14px; 
            margin-top: 8px;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .add-btn { 
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            box-shadow: 0 5px 20px rgba(72, 187, 120, 0.3);
        }
        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
        }
        .remove-btn { 
            background: linear-gradient(135deg, #fc8181 0%, #f56565 100%);
            color: white;
            box-shadow: 0 5px 20px rgba(252, 129, 129, 0.3);
        }
        .remove-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(252, 129, 129, 0.4);
        }
        .travelers-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .roommates-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-note { 
            font-size: 0.875rem; 
            color: #718096; 
            margin-top: 8px; 
            font-style: italic;
            line-height: 1.5;
        }
        .relationship-container { display: block; }
        .relationship-container select { width: 100%; margin-bottom: 0; }
        .other-relationship { 
            margin-top: 15px; 
            display: block;
            width: 100%;
        }
        .other-relationship label {
            display: block;
            margin-bottom: 8px;
        }
        .other-relationship input {
            width: 100%;
        }
        .passport-other { margin-top: 15px; }
        .file-preview { 
            margin-top: 15px;
            padding: 15px;
            background: #f7fafc;
            border-radius: 12px;
            border: 2px dashed #cbd5e0;
        }
        .file-preview img { 
            max-width: 100%; 
            max-height: 250px; 
            border-radius: 12px; 
            border: 3px solid #e2e8f0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        input[type="file"] { 
            padding: 20px; 
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 3px dashed #cbd5e0; 
            border-radius: 12px; 
            cursor: pointer;
            transition: all 0.3s ease;
        }
        input[type="file"]:hover { 
            border-color: #667eea; 
            background: linear-gradient(135deg, #edf2f7 0%, #e2e8f0 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
        }
        .share-button { 
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white; 
            padding: 14px 30px; 
            border-radius: 12px; 
            border: none; 
            font-size: 16px; 
            font-weight: 600; 
            cursor: pointer; 
            display: inline-flex; 
            align-items: center; 
            gap: 12px; 
            margin-top: 20px;
            box-shadow: 0 10px 30px rgba(37, 211, 102, 0.3);
            transition: all 0.3s ease;
        }
        .share-button:hover { 
            transform: translateY(-4px);
            box-shadow: 0 15px 40px rgba(37, 211, 102, 0.4);
        }
        @media (max-width: 768px) { 
            .form-container { grid-template-columns: 1fr; } 
            .form-row, .form-row-3 { grid-template-columns: 1fr; }
            .travelers-grid, .roommates-grid { grid-template-columns: 1fr; }
            h1 { font-size: 1.8rem; } 
        }
        .success-message { 
            background-color: #d4edda; 
            color: #155724; 
            padding: 15px; 
            border-radius: 6px; 
            margin-bottom: 20px; 
            display: none; 
            border-left: 4px solid #28a745;
        }
        .error-message { 
            background-color: #f8d7da; 
            color: #721c24; 
            padding: 15px; 
            border-radius: 6px; 
            margin-bottom: 20px; 
            display: none;
            border-left: 4px solid #dc3545;
        }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 10% auto; padding: 30px; border-radius: 10px; width: 80%; max-width: 600px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .close-modal { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close-modal:hover { color: #000; }
        input[readonly], select[disabled], textarea[readonly] {
            background-color: #f7fafc !important;
            cursor: not-allowed;
        }
        .back-btn {
            background: white;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            color: #667eea;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        .back-btn:hover {
            background: #f7fafc;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="customers.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Customers
        </a>
        
        <header>
            <div class="logo">
                <i class="fas fa-kaaba"></i>
            </div>
            <h1>Hajj 2026 Registration</h1>
            <p class="subtitle">Awaisi Tours • Complete your pilgrimage registration</p>
            <div style="margin-top: 25px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                <div style="background: rgba(102, 126, 234, 0.1); padding: 10px 20px; border-radius: 25px; font-size: 0.9rem; color: #667eea; font-weight: 600;">
                    <i class="fas fa-shield-alt" style="margin-right: 8px;"></i>Secure Registration
                </div>
                <div style="background: rgba(102, 126, 234, 0.1); padding: 10px 20px; border-radius: 25px; font-size: 0.9rem; color: #667eea; font-weight: 600;">
                    <i class="fas fa-clock" style="margin-right: 8px;"></i>Quick & Easy
                </div>
                <div style="background: rgba(102, 126, 234, 0.1); padding: 10px 20px; border-radius: 25px; font-size: 0.9rem; color: #667eea; font-weight: 600;">
                    <i class="fas fa-headset" style="margin-right: 8px;"></i>24/7 Support
                </div>
            </div>
        </header>
        
        <div class="instructions">
            <h3><i class="fas fa-info-circle"></i> Important Information (Please Read)</h3>
            <ul style="list-style-type: disc; margin-left: 20px;">
                <li>Fields marked with <span style="color: #e53e3e; font-weight: 700;">*</span> are required</li>
                <li>Upload the first 2 pages of your passport (JPEG or PNG, max 1MB)</li>
                <li>Upload one passport photo with a white background (JPEG or PNG, max 18KB)</li>
                <li>Make sure all passport details match your official documents exactly</li>
                <li>Your emergency contact must be in the same country where you live</li>
                <li>Check all details carefully before submitting</li>
            </ul>
        </div>

        <div class="success-message" id="successMessage">
            <i class="fas fa-check-circle"></i> Form submitted successfully! You will receive a confirmation email shortly.
        </div>

        <div class="error-message" id="errorMessage">
            <i class="fas fa-exclamation-circle"></i> Please fill in all required fields correctly.
        </div>
        
        <!-- FORM -->
        <form id="hajjForm" style="pointer-events: none;">
            <div class="form-container">
                <!-- Personal Information Section -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-user"></i> Personal Details</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="agent" class="required">Booking Agent</label>
                            <select id="agent" name="agent" disabled>
                                <option value="">Select Booking Agent</option>
                                <option value="Mirza Travels (UK)" <?php echo $customer['booking_agent'] == 'Mirza Travels (UK)' ? 'selected' : ''; ?>>Mirza Travels (UK)</option>
                                <option value="Al Ghani – Yasir Attari Bai (UK)" <?php echo $customer['booking_agent'] == 'Al Ghani – Yasir Attari Bai (UK)' ? 'selected' : ''; ?>>Al Ghani – Yasir Attari Bai (UK)</option>
                                <option value="Al Amin Spiritual (UK & Europe)" <?php echo $customer['booking_agent'] == 'Al Amin Spiritual (UK & Europe)' ? 'selected' : ''; ?>>Al Amin Spiritual (UK & Europe)</option>
                                <option value="Mirza Amin (UK, Belgium, Norway)" <?php echo $customer['booking_agent'] == 'Mirza Amin (UK, Belgium, Norway)' ? 'selected' : ''; ?>>Mirza Amin (UK, Belgium, Norway)</option>
                                <option value="Hamza Abdoelbasier (Holland)" <?php echo $customer['booking_agent'] == 'Hamza Abdoelbasier (Holland)' ? 'selected' : ''; ?>>Hamza Abdoelbasier (Holland)</option>
                                <option value="Hussain Abdoelbasier (Holland)" <?php echo $customer['booking_agent'] == 'Hussain Abdoelbasier (Holland)' ? 'selected' : ''; ?>>Hussain Abdoelbasier (Holland)</option>
                                <option value="Hafiz Tahir (Portugal, Italy, Germany & France)" <?php echo $customer['booking_agent'] == 'Hafiz Tahir (Portugal, Italy, Germany & France)' ? 'selected' : ''; ?>>Hafiz Tahir (Portugal, Italy, Germany & France)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="title" class="required">Title</label>
                            <select id="title" name="title" disabled>
                                <option value="">Select Title</option>
                                <option value="Mr" <?php echo $customer['title'] == 'Mr' ? 'selected' : ''; ?>>Mr</option>
                                <option value="Mrs" <?php echo $customer['title'] == 'Mrs' ? 'selected' : ''; ?>>Mrs</option>
                                <option value="Miss" <?php echo $customer['title'] == 'Miss' ? 'selected' : ''; ?>>Miss</option>
                                <option value="Ms" <?php echo $customer['title'] == 'Ms' ? 'selected' : ''; ?>>Ms</option>
                                <option value="Dr" <?php echo $customer['title'] == 'Dr' ? 'selected' : ''; ?>>Dr</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="firstName" class="required">First Name</label>
                        <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($customer['first_name']); ?>" readonly>
                    </div>
                    
                    <div class="form-row-3">
                        <div class="form-group">
                            <label for="middleName1">Middle Name 1</label>
                            <input type="text" id="middleName1" name="middleName1" value="<?php echo htmlspecialchars($customer['middle_name1'] ?? ''); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="middleName2">Middle Name 2</label>
                            <input type="text" id="middleName2" name="middleName2" value="<?php echo htmlspecialchars($customer['middle_name2'] ?? ''); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="middleName3">Middle Name 3</label>
                            <input type="text" id="middleName3" name="middleName3" value="<?php echo htmlspecialchars($customer['middle_name3'] ?? ''); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="lastName" class="required">Last Name</label>
                        <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($customer['last_name']); ?>" readonly>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dob" class="required">Date of Birth</label>
                            <input type="date" id="dob" name="dob" value="<?php echo $customer['date_of_birth']; ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="pob" class="required">Place of Birth</label>
                            <input type="text" id="pob" name="pob" value="<?php echo htmlspecialchars($customer['place_of_birth']); ?>" readonly>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information Section -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-address-card"></i> Contact Details</h3>
                    
                    <div class="form-group">
                        <label for="email" class="required">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" readonly>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" style="flex: 0 0 200px;">
                            <label for="mobileCountryCode" class="required">Country Dialing Code</label>
                            <select id="mobileCountryCode" name="mobileCountryCode" disabled style="width: 100%;">
                                <option value="">Select Code</option>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?php echo htmlspecialchars($country['dial_code']); ?>" 
                                            <?php echo $mobileCountryCode == $country['dial_code'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($country['dial_code'] . ' (' . $country['name'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="mobile" class="required">Mobile Number</label>
                            <input type="tel" id="mobile" name="mobile" value="<?php echo htmlspecialchars($mobileNumber); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="required">House Number & Street Name</label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($customer['address']); ?>" readonly>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="town" class="required">Town/City</label>
                            <input type="text" id="town" name="town" value="<?php echo htmlspecialchars($customer['town']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="district" class="required">District/County/Province</label>
                            <input type="text" id="district" name="district" value="<?php echo htmlspecialchars($customer['district'] ?? ''); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="postcode" class="required">Postcode</label>
                            <input type="text" id="postcode" name="postcode" value="<?php echo htmlspecialchars($customer['postcode']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="country" class="required">Country</label>
                            <select id="country" name="country" disabled>
                                <option value="">Select Country</option>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?php echo htmlspecialchars($country['name']); ?>" 
                                            <?php echo ($customer['country'] ?? '') == $country['name'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($country['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Emergency Contact Section -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-phone-alt"></i> Emergency Contact (Country of Residence)</h3>
                    
                    <div class="form-group">
                        <label for="emergencyName" class="required">Emergency Contact Name</label>
                        <input type="text" id="emergencyName" name="emergencyName" value="<?php echo htmlspecialchars($customer['emergency_name']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="emergencyAddress" class="required">Emergency Contact Address</label>
                        <textarea id="emergencyAddress" name="emergencyAddress" rows="3" readonly><?php echo htmlspecialchars($customer['emergency_address']); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" style="flex: 0 0 200px;">
                            <label for="emergencyCountryCode" class="required">Country Dialing Code</label>
                            <select id="emergencyCountryCode" name="emergencyCountryCode" disabled style="width: 100%;">
                                <option value="">Select Code</option>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?php echo htmlspecialchars($country['dial_code']); ?>" 
                                            <?php echo $emergencyCountryCode == $country['dial_code'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($country['dial_code'] . ' (' . $country['name'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="emergencyContact" class="required">Emergency Contact Number</label>
                            <input type="tel" id="emergencyContact" name="emergencyContact" value="<?php echo htmlspecialchars($emergencyContactNumber); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="emergencyCountry" class="required">Country</label>
                            <select id="emergencyCountry" name="emergencyCountry" disabled>
                                <option value="">Select Country</option>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?php echo htmlspecialchars($country['name']); ?>" 
                                            <?php echo ($customer['emergency_country'] ?? '') == $country['name'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($country['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="emergencyRelationship" class="required">Relationship</label>
                            <?php 
                            // Determine if relationship is "Other"
                            $standardRelationships = ['Father', 'Mother', 'Sister', 'Brother', 'Daughter', 'Son', 'Aunty', 'Uncle', 'Grandad', 'Grandmother'];
                            $isOther = !empty($customer['emergency_relationship']) && !in_array($customer['emergency_relationship'], $standardRelationships);
                            ?>
                            <div class="relationship-container">
                                <select id="emergencyRelationship" name="emergencyRelationship" disabled>
                                    <option value="">Select Relationship</option>
                                    <option value="Father" <?php echo $customer['emergency_relationship'] == 'Father' ? 'selected' : ''; ?>>Father</option>
                                    <option value="Mother" <?php echo $customer['emergency_relationship'] == 'Mother' ? 'selected' : ''; ?>>Mother</option>
                                    <option value="Sister" <?php echo $customer['emergency_relationship'] == 'Sister' ? 'selected' : ''; ?>>Sister</option>
                                    <option value="Brother" <?php echo $customer['emergency_relationship'] == 'Brother' ? 'selected' : ''; ?>>Brother</option>
                                    <option value="Daughter" <?php echo $customer['emergency_relationship'] == 'Daughter' ? 'selected' : ''; ?>>Daughter</option>
                                    <option value="Son" <?php echo $customer['emergency_relationship'] == 'Son' ? 'selected' : ''; ?>>Son</option>
                                    <option value="Aunty" <?php echo $customer['emergency_relationship'] == 'Aunty' ? 'selected' : ''; ?>>Aunty</option>
                                    <option value="Uncle" <?php echo $customer['emergency_relationship'] == 'Uncle' ? 'selected' : ''; ?>>Uncle</option>
                                    <option value="Grandad" <?php echo $customer['emergency_relationship'] == 'Grandad' ? 'selected' : ''; ?>>Grandad</option>
                                    <option value="Grandmother" <?php echo $customer['emergency_relationship'] == 'Grandmother' ? 'selected' : ''; ?>>Grandmother</option>
                                    <option value="Other" <?php echo $isOther ? 'selected' : ''; ?>>Other</option>
                                </select>
                                
                                <?php if ($isOther): ?>
                                <div class="other-relationship" id="otherRelationshipContainer" style="display: block;">
                                    <label for="otherRelationship">Specify Relationship</label>
                                    <input type="text" id="otherRelationship" name="otherRelationship" value="<?php echo htmlspecialchars($customer['emergency_relationship']); ?>" readonly>
                                </div>
                                <?php else: ?>
                                <div class="other-relationship" id="otherRelationshipContainer" style="display: none;">
                                    <label for="otherRelationship">Specify Relationship</label>
                                    <input type="text" id="otherRelationship" name="otherRelationship" placeholder="Enter relationship" readonly>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Passport & Travel Details Section -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-passport"></i> Passport Details</h3>
                    
                    <div class="form-group">
                        <label for="passportNumber" class="required">Passport Number</label>
                        <input type="text" id="passportNumber" name="passportNumber" value="<?php echo htmlspecialchars($customer['passport_number']); ?>" readonly>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="issueDate" class="required">Issue Date</label>
                            <input type="date" id="issueDate" name="issueDate" value="<?php echo $customer['passport_issue_date']; ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="expiryDate" class="required">Expiry Date</label>
                            <input type="date" id="expiryDate" name="expiryDate" value="<?php echo $customer['passport_expiry_date']; ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="passportCountryOfIssue" class="required">Passport Country of Issue</label>
                        <select id="passportCountryOfIssue" name="passportCountryOfIssue" disabled>
                            <option value="">Select Country</option>
                            <?php foreach ($countries as $country): ?>
                                <option value="<?php echo htmlspecialchars($country['name']); ?>" 
                                        <?php echo ($customer['passport_country_of_issue'] ?? $customer['passport_type'] ?? '') == $country['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($country['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="passportPicture" class="required">Passport Picture</label>
                            <?php if ($customer['passport_picture']): ?>
                                <div class="file-preview" style="display: block; margin-top: 10px;">
                                    <img src="../../uploads/<?php echo htmlspecialchars($customer['passport_picture']); ?>" 
                                         alt="Passport Picture" 
                                         style="max-width: 200px; max-height: 200px; border-radius: 6px; border: 2px solid #ddd;">
                                    <br><br>
                                    <a href="../../uploads/<?php echo htmlspecialchars($customer['passport_picture']); ?>" download class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                            </div>
                            <?php else: ?>
                                <p class="form-note">No passport picture uploaded</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="passportDocument" class="required">Passport Document (First 2 Pages)</label>
                            <?php if ($customer['passport_document']): ?>
                                <div class="file-preview" style="display: block; margin-top: 10px;">
                                    <div style="padding: 10px; background: #f8f9fa; border-radius: 6px; border: 2px solid #ddd;">
                                        <i class="fas fa-file"></i> Document uploaded
                                </div>
                                    <br>
                                    <a href="../../uploads/<?php echo htmlspecialchars($customer['passport_document']); ?>" download class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i> Download Document
                                    </a>
                            </div>
                            <?php else: ?>
                                <p class="form-note">No passport document uploaded</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Travel Companions Section -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-users"></i> Travel Companions</h3>
                    <p class="form-note">List people traveling with you (maximum 10). If traveling alone, leave blank.</p>
                    
                    <div class="input-group" id="travelCompanionsGroup">
                        <div class="travelers-grid" id="travelCompanionsContainer">
                            <?php if (!empty($travelCompanions)): ?>
                                <?php foreach ($travelCompanions as $index => $companion): ?>
                                    <div class="form-group">
                                        <label>Travel Companion <?php echo $index + 1; ?></label>
                                        <input type="text" value="<?php echo htmlspecialchars($companion); ?>" readonly>
                        </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="form-note" style="grid-column: 1 / -1;">No travel companions listed</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Accommodation Preferences Section -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-bed"></i> Accommodation Preferences</h3>
                    
                    <div class="form-group">
                        <label for="package" class="required">Package Selection</label>
                        <select id="package" name="package" disabled>
                            <option value="">Select Package</option>
                            <option value="Standard" <?php echo $customer['package_name'] == 'Standard' ? 'selected' : ''; ?>>Standard</option>
                            <option value="Premium" <?php echo $customer['package_name'] == 'Premium' ? 'selected' : ''; ?>>Premium</option>
                            <option value="Luxury" <?php echo $customer['package_name'] == 'Luxury' ? 'selected' : ''; ?>>Luxury</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="roomSize" class="required">Room Type</label>
                        <input type="text" id="roomSize" name="roomSize" value="<?php echo htmlspecialchars($customer['room_type']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="roomSharing" class="required">Room Sharing Preferences</label>
                        <p class="form-note">Enter names of people you wish to share room with (2-4 names)</p>
                        
                        <div class="input-group" id="roommatesGroup">
                            <div class="roommates-grid" id="roommatesContainer">
                                <?php if (!empty($roommates)): ?>
                                    <?php foreach ($roommates as $index => $roommate): ?>
                                        <div class="form-group">
                                            <label>Roommate <?php echo $index + 1; ?></label>
                                            <input type="text" value="<?php echo htmlspecialchars($roommate); ?>" readonly>
                            </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="form-note" style="grid-column: 1 / -1;">No roommates listed</p>
                                <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            
        </form>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="customers.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Customers
            </a>
        </div>
        
        <footer>
            <p>Hajj 2026 Registration System | Awaisi Tours</p>
            <p>For assistance, contact: awaisitours@gmail.com | <i class="far fa-copyright"></i> 2026</p>
        </footer>
    </div>

    <script>
        // This is a readonly view - no JavaScript needed
    </script>
</body>
</html>