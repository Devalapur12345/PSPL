<?php
// Start a session if one is not already active
if (!isset($_SESSION)) {
    session_start();
}
require_once 'dbconnPostgres.php';

// Fix: Define the $access variable to prevent "Undefined variable" warnings.
// In a production environment, this value should be set based on user authentication.
$access = 1; // Assuming a value of 1 grants access

// Function to safely format date strings for the database (DD-MM-YYYY to YYYY-MM-DD)
function formatDateForDatabase($dateString) {
    if (empty($dateString)) {
        return null; // Return null for empty dates
    }
    // Attempt to parse the date from DD-MM-YYYY format
    $date = DateTime::createFromFormat('d-m-Y', $dateString);
    if ($date) {
        return $date->format('Y-m-d');
    }
    return null; // Return null if parsing fails
}

// Function to safely format date strings for display (YYYY-MM-DD to DD-MM-YYYY)
function formatDateForDisplay($dateString) {
    if (empty($dateString)) {
        return ''; // Return an empty string for empty dates
    }
    // Attempt to parse the date from YYYY-MM-DD format
    $date = DateTime::createFromFormat('Y-m-d', $dateString);
    if ($date) {
        return $date->format('d-m-Y');
    }
    return ''; // Return an empty string if parsing fails
}

// Function to safely get float values
function getFloatValue($value) {
    // Return 0.0 if the value is empty or not a number
    return empty($value) ? 0.0 : (float)$value;
}

// Initialize variables to store form values and messages
$message = '';
$editData = null; // Variable to hold data for editing
$ncodeValue = 0; // Initialize ncode to 0 for new entries
$creationDateValue = date('d-m-Y');
$txtDateValue = date('d-m-Y');

// Handle GET request for editing a record
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $sql = "SELECT * FROM account_heads WHERE ncode = $1";
    $result = pg_query_params($conn, $sql, array($editId));
    
    if ($result) {
        $editData = pg_fetch_assoc($result);
        if ($editData) {
            // Populate the date variables with the fetched data, formatted for display
            $creationDateValue = formatDateForDisplay($editData['txtcreatedate']);
            $txtDateValue = formatDateForDisplay($editData['txtdate']);
            $ncodeValue = $editData['ncode'];
            // Populate all other form variables
            foreach ($editData as $key => $value) {
                $$key = $value;
            }
        } else {
            $message = "No record found with ncode: " . htmlspecialchars($editId);
        }
    } else {
        $message = "Error fetching data: " . pg_last_error($conn);
    }
}

// Check if the form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize string inputs for security.
    $groupcode = isset($_POST['groupcode']) ? pg_escape_string($conn, $_POST['groupcode']) : '';
    $level1 = isset($_POST['level1']) ? pg_escape_string($conn, $_POST['level1']) : '';
    $cmbcontrolhead = isset($_POST['cmbcontrolhead']) ? pg_escape_string($conn, $_POST['cmbcontrolhead']) : '';
    $txtinitials1 = isset($_POST['txtinitials1']) ? pg_escape_string($conn, $_POST['txtinitials1']) : '';
    $txtcode = isset($_POST['txtcode']) ? pg_escape_string($conn, $_POST['txtcode']) : '';
    $txtdesc = isset($_POST['txtdesc']) ? pg_escape_string($conn, $_POST['txtdesc']) : '';

    $txtcreatedate = formatDateForDatabase(isset($_POST['txtcreatedate']) ? $_POST['txtcreatedate'] : '');
    $txtgstrate = formatDateForDatabase(isset($_POST['txtgstrate']) ? $_POST['txtgstrate'] : '');

    $txtopnbal = getFloatValue(isset($_POST['txtopnbal']) ? $_POST['txtopnbal'] : 0);
    $txtyropnbal = getFloatValue(isset($_POST['txtyropnbal']) ? $_POST['txtyropnbal'] : 0);
    $txtfcopnbal = getFloatValue(isset($_POST['txtfcopnbal']) ? $_POST['txtfcopnbal'] : 0);

    $txtname = isset($_POST['txtname']) ? pg_escape_string($conn, $_POST['txtname']) : '';
    $txtaddress1 = isset($_POST['txtaddress1']) ? pg_escape_string($conn, $_POST['txtaddress1']) : '';
    $txtaddress2 = isset($_POST['txtaddress2']) ? pg_escape_string($conn, $_POST['txtaddress2']) : '';
    $txtaddress3 = isset($_POST['txtaddress3']) ? pg_escape_string($conn, $_POST['txtaddress3']) : '';
    $txtaddress4 = isset($_POST['txtaddress4']) ? pg_escape_string($conn, $_POST['txtaddress4']) : '';
    $txtshipaddr1 = isset($_POST['txtshipaddr1']) ? pg_escape_string($conn, $_POST['txtshipaddr1']) : '';
    $txtshipaddr2 = isset($_POST['txtshipaddr2']) ? pg_escape_string($conn, $_POST['txtshipaddr2']) : '';
    $txtshipaddr3 = isset($_POST['txtshipaddr3']) ? pg_escape_string($conn, $_POST['txtshipaddr3']) : '';
    $txtshipaddr4 = isset($_POST['txtshipaddr4']) ? pg_escape_string($conn, $_POST['txtshipaddr4']) : '';
    $txtfax = isset($_POST['txtfax']) ? pg_escape_string($conn, $_POST['txtfax']) : '';
    $txtplacestate2 = isset($_POST['txtplacestate2']) ? pg_escape_string($conn, $_POST['txtplacestate2']) : '';
    $txtcontactper = isset($_POST['txtcontactper']) ? pg_escape_string($conn, $_POST['txtcontactper']) : '';
    $txtpinno = isset($_POST['txtpinno']) ? pg_escape_string($conn, $_POST['txtpinno']) : '';
    $txtcommercialcontper = isset($_POST['txtcommercialcontper']) ? pg_escape_string($conn, $_POST['txtcommercialcontper']) : '';
    $txtphone = isset($_POST['txtphone']) ? pg_escape_string($conn, $_POST['txtphone']) : '';
    $txtgstcontact = isset($_POST['txtgstcontact']) ? pg_escape_string($conn, $_POST['txtgstcontact']) : '';
    $txtemail = isset($_POST['txtemail']) ? pg_escape_string($conn, $_POST['txtemail']) : '';

    // Correctly retrieve the combined PAN and GST numbers from the hidden fields
    $txtpanno = isset($_POST['txtpanno']) ? pg_escape_string($conn, $_POST['txtpanno']) : '';
    $txtgstno = isset($_POST['txtgstno']) ? pg_escape_string($conn, $_POST['txtgstno']) : '';

    $txtpanpercentage = isset($_POST['txtpanpercentage']) ? pg_escape_string($conn, $_POST['txtpanpercentage']) : '';
    $txtgsttype = isset($_POST['txtgsttype']) ? pg_escape_string($conn, $_POST['txtgsttype']) : '';
    $activeb = isset($_POST['activeb']) ? 't' : 'f'; // Corrected to 't' for true, 'f' for false
    
    $txtrcno = isset($_POST['txtrcno']) ? pg_escape_string($conn, $_POST['txtrcno']) : '';
    $txtcstno = isset($_POST['txtcstno']) ? pg_escape_string($conn, $_POST['txtcstno']) : '';
    $txtrange = isset($_POST['txtrange']) ? pg_escape_string($conn, $_POST['txtrange']) : '';
    $txtdate = formatDateForDatabase(isset($_POST['txtdate']) ? $_POST['txtdate'] : '');
    $txtdivision = isset($_POST['txtdivision']) ? pg_escape_string($conn, $_POST['txtdivision']) : '';
    $txttype = isset($_POST['txttype']) ? pg_escape_string($conn, $_POST['txttype']) : '';
    
    // Check the value of the 'action' hidden input to determine the operation
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $ncode = isset($_POST['ncode']) ? (int)$_POST['ncode'] : null;

    if ($action === 'save' || $action === 'modify') {
        // Prepare parameters for both INSERT and UPDATE
        $params = array(
            $groupcode, $level1, $cmbcontrolhead, $txtinitials1, $txtcode,
            $txtdesc, $txtcreatedate, $txtopnbal, $txtyropnbal, $txtfcopnbal,
            $txtname, $txtaddress1, $txtaddress2, $txtaddress3, $txtaddress4,
            $txtshipaddr1, $txtshipaddr2, $txtshipaddr3, $txtshipaddr4, $txtfax,
            $txtplacestate2, $txtcontactper, $txtpinno, $txtcommercialcontper,
            $txtphone, $txtgstcontact, $txtemail, $txtpanno, $txtpanpercentage,
            $txtgstno, $txtgstrate, $txtgsttype, $activeb,
            $txtrcno, $txtcstno, $txtrange, $txtdate, $txtdivision, $txttype
        );
        
        // This is an UPDATE operation
        if ($ncode > 0) { 
            $sql = "UPDATE account_heads SET
                groupcode=$1, level1=$2, cmbcontrolhead=$3, txtinitials1=$4, txtcode=$5, txtdesc=$6,
                txtcreatedate=$7, txtopnbal=$8, txtyropnbal=$9, txtfcopnbal=$10,
                txtname=$11, txtaddress1=$12, txtaddress2=$13, txtaddress3=$14, txtaddress4=$15,
                txtshipaddr1=$16, txtshipaddr2=$17, txtshipaddr3=$18, txtshipaddr4=$19, txtfax=$20,
                txtplacestate2=$21, txtcontactper=$22, txtpinno=$23, txtcommercialcontper=$24,
                txtphone=$25, txtgstcontact=$26, txtemail=$27, txtpanno=$28, txtpanpercentage=$29,
                txtgstno=$30, txtgstrate=$31, txtgsttype=$32, activeb=$33,
                txtrcno=$34, txtcstno=$35, txtrange=$36, txtdate=$37, txtdivision=$38, txttype=$39
                WHERE ncode = $40";
            
            $params[] = $ncode;
            $result = pg_query_params($conn, $sql, $params);

            if ($result) {
                $message = "Data updated successfully!";
                header("Location: accounthead.php?edit_id=" . $ncode);
                exit();
            } else {
                $message = "Error updating data: " . pg_last_error($conn);
            }

        } else {
            // This is an INSERT operation
            $sql = "INSERT INTO account_heads (
                groupcode, level1, cmbcontrolhead, txtinitials1, txtcode, txtdesc, txtcreatedate,
                txtopnbal, txtyropnbal, txtfcopnbal, txtname, txtaddress1, txtaddress2,
                txtaddress3, txtaddress4, txtshipaddr1, txtshipaddr2, txtshipaddr3,
                txtshipaddr4, txtfax, txtplacestate2, txtcontactper, txtpinno,
                txtcommercialcontper, txtphone, txtgstcontact, txtemail, txtpanno,
                txtpanpercentage, txtgstno, txtgstrate, txtgsttype, activeb,
                txtrcno, txtcstno, txtrange, txtdate, txtdivision, txttype
            ) VALUES (
                $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14,
                $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, $26,
                $27, $28, $29, $30, $31, $32, $33, $34, $35, $36, $37, $38,
                $39
            )";
            
            $result = pg_query_params($conn, $sql, $params);
            if ($result) {
                $message = "Data saved successfully!";
                header("Location: accounthead.php");
                exit();
            } else {
                $message = "Error saving data: " . pg_last_error($conn);
            }
        }
    } elseif ($action === 'delete' && $ncode) {
        // This is a DELETE operation
        $sql = "DELETE FROM account_heads WHERE ncode = $1";
        $result = pg_query_params($conn, $sql, array($ncode));

        if ($result) {
            $message = "Record deleted successfully!";
            header("Location: accounthead.php");
            exit();
        } else {
            $message = "Error deleting record: " . pg_last_error($conn);
        }
    }
}

// After a POST operation, reload the edit data to reflect the changes or clear the form
if ($_SERVER["REQUEST_METHOD"] == "POST" && $message && ($action === 'save' || $action === 'modify' || $action === 'delete')) {
    // We already redirected above, so this block won't be reached
} elseif (isset($_GET['edit_id']) && $editData) {
    // If the page is loaded with an edit_id and data was found, populate the form
    $ncodeValue = $editData['ncode'];
    $groupcode = $editData['groupcode'];
    $level1 = $editData['level1'];
    $cmbcontrolhead = $editData['cmbcontrolhead'];
    $txtinitials1 = $editData['txtinitials1'];
    $txtcode = $editData['txtcode'];
    $txtdesc = $editData['txtdesc'];
    $creationDateValue = formatDateForDisplay($editData['txtcreatedate']);
    $txtopnbal = $editData['txtopnbal'];
    $txtyropnbal = $editData['txtyropnbal'];
    $txtfcopnbal = $editData['txtfcopnbal'];
    $txtname = $editData['txtname'];
    $txtaddress1 = $editData['txtaddress1'];
    $txtaddress2 = $editData['txtaddress2'];
    $txtaddress3 = $editData['txtaddress3'];
    $txtaddress4 = $editData['txtaddress4'];
    $txtshipaddr1 = $editData['txtshipaddr1'];
    $txtshipaddr2 = $editData['txtshipaddr2'];
    $txtshipaddr3 = $editData['txtshipaddr3'];
    $txtshipaddr4 = $editData['txtshipaddr4'];
    $txtfax = $editData['txtfax'];
    $txtplacestate2 = $editData['txtplacestate2'];
    $txtcontactper = $editData['txtcontactper'];
    $txtpinno = $editData['txtpinno'];
    $txtcommercialcontper = $editData['txtcommercialcontper'];
    $txtphone = $editData['txtphone'];
    $txtgstcontact = $editData['txtgstcontact'];
    $txtemail = $editData['txtemail'];
    $txtpanno = $editData['txtpanno'];
    $txtpanpercentage = $editData['txtpanpercentage'];
    $txtgstno = $editData['txtgstno'];
    $txtgstrate = $editData['txtgstrate'];
    $txtgsttype = $editData['txtgsttype'];
    $activeb = $editData['activeb'];
    $txtrcno = $editData['txtrcno'];
    $txtcstno = $editData['txtcstno'];
    $txtrange = $editData['txtrange'];
    $txtDateValue = formatDateForDisplay($editData['txtdate']);
    $txtdivision = $editData['txtdivision'];
    $txttype = $editData['txttype'];
}

// Close the database connection at the end of the script
pg_close($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Head Master</title>
    <link rel="SHORTCUT ICON" HREF="images/logo.png">

    <link href="css/stylesheet.css" type="text/css" rel="stylesheet">
    <link href="css/dimensions.css" type="text/css" rel="stylesheet">
    <link href="css/style.css" type="text/css" rel="stylesheet">
    <link href="css/jquery.autocomplete.css" type="text/css" rel="stylesheet">
    <link href="css/crumbs_ak.css" type="text/css" rel="stylesheet">
    
    <style type="text/css">
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f0f2f5;
    }
    #wrapper {
        flex-grow: 1; 
        max-width: 1200px;
        margin: 20px auto;
        padding: 20px;
        background-color: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        border: 1px solid #000;
        overflow: hidden;
    }
    .section-heading {
        background-color: #34495e;
        color: white;
        padding: 15px;
        font-size: 1.25em;
        text-align: center;
        border-radius: 6px 6px 0 0;
        margin: 0 0 20px 0;
    }

    /* ✅ Scoped form layout so header.css cannot break it */
    #wrapper form .form-section {
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 20px;
        background-color: #fafafa;
    }
    #wrapper form .form-container {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 20px;
    }
    #wrapper form .form-field {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    #wrapper form .form-label {
        color: #4d4d4d;
        font-size: 0.8em;
        white-space: nowrap;
        flex-basis: 150px;
        text-align: right;
        font-weight: bold;
    }
    #wrapper form .form-input {
        flex-grow: 1;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    #wrapper form .text-input, 
    #wrapper form .select-input {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        transition: border-color 0.3s;
    }
    #wrapper form .text-input:focus, 
    #wrapper form .select-input:focus {
        outline: none;
        border-color: #036A91;
    }
    #wrapper form .icon-wrapper {
        display: flex;
        align-items: center;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #fff;
        width: 100%;
    }
    #wrapper form .icon-wrapper input {
        border: none;
        flex-grow: 1;
    }
    #wrapper form .icon-wrapper img {
        cursor: pointer;
        padding: 5px;
        height: 18px;
    }
    #wrapper form .gst-box {
        width: 25px !important;
        padding: 8px 2px;
        text-align: center;
        margin: 0 1px;
    }
    #wrapper form .full-width {
        grid-column: 1 / -1;
    }
    #wrapper form .button-group {
        text-align: center;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
        margin-top: 20px;
    }
    #wrapper form .btn {
        background-color: #1F95C0;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9em;
        margin: 0 5px;
        transition: background-color 0.3s;
    }
    #wrapper form .btn:hover {
        background-color: #036A91;
    }
    #wrapper form .radio-group {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    /* ✅ Icon wrapper */
#wrapper form .icon-wrapper {
    display: flex;
    align-items: center;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: #fff;
    width: 100%;
    padding-right: 5px;
}

/* ✅ Input inside wrapper */
#wrapper form .icon-wrapper input {
    border: none;
    flex-grow: 1;
    padding: 8px;
}

/* ✅ Image icons corrected */
#wrapper form .icon-wrapper img {
    cursor: pointer;
    padding: 4px;
    height: 24px;   /* fixed size */
    width: 24px;    /* keeps square ratio */
}


/* ✅ Add this new style for the bank details section to match the design */
.bank-section {
    border: 1px solid #cce8f0; /* Light blue border */
    border-radius: 8px; /* Rounded corners */
    padding: 20px;
    margin: 20px 0;
    background-color: #f0f8ff; /* Very light blue background */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); /* Soft shadow */
    text-align: left; /* Aligns the content to the left inside the box */
}

.bank-section h3 {
    text-align: center;
    color: #036A91;
    font-size: 1.2em;
    font-weight: bold;
    margin-bottom: 20px;
}

.bank-details-grid {
    display: flex;
    flex-direction: column;
    gap: 15px; /* Spacing between each label-input pair */
}

.bank-details-grid .form-field {
    display: flex;
    flex-direction: row; /* Keep label and input on the same row */
    align-items: center;
    gap: 10px;
}

.bank-details-grid .form-label {
    flex: 0 0 120px; /* Fixed width for the label */
    text-align: right;
    font-weight: normal;
    font-size: 0.9em;
}

.bank-details-grid .form-input {
    flex: 1; /* Input takes up the remaining space */
}

/* Make sure the text-input inside the bank form is styled correctly */
.bank-details-grid .text-input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 1em;
}

/* The button styles remain the same from the main form CSS */
#btnBank {
    /* Existing btn styles */
}


    </style>

    <script type="text/javascript" src="js/datetimepicker.js"></script>
    <script type="text/javascript" src="js/ak/jquery.js"></script>
    <script type="text/javascript" src="js/ak/jquerymigrate.js"></script>
    <script type="text/javascript" src="js/ak/jquery.autocomplete.js"></script>
    <script type="text/javascript" src="js/accountHead.js?kill=<?php echo rand(0, 100); ?>"></script>
    <script type="text/javascript" src="js/othfunctionality.js"></script>
    <script type="text/javascript" src="js/global.js"></script>

    <script>
        function concurrententry() {
            window.setTimeout(function() {
                document.getElementById('txtname').value = document.getElementById('txtdesc').value;
            }, 0);
        }

        // This function will automatically move the cursor to the next input box.
        function focusNextInput(currentInput, prefix, currentIndex, totalInputs) {
            if (currentInput.value.length === currentInput.maxLength) {
                let nextIndex = currentIndex + 1;
                if (nextIndex <= totalInputs) {
                    let nextInput = document.getElementById(prefix + nextIndex);
                    if (nextInput) {
                        nextInput.focus();
                    }
                }
            }
        }

        // New function to combine PAN and GST inputs before form submission
        function combinePanGst() {
            let panValue = '';
            for (let i = 0; i <= 9; i++) {
                const panInput = document.getElementById('pan' + i);
                if (panInput) {
                    panValue += panInput.value;
                }
            }
            document.getElementById('combined_panno').value = panValue;

            let gstValue = '';
            for (let i = 0; i <= 14; i++) {
                const gstInput = document.getElementById('gst' + i);
                if (gstInput) {
                    gstValue += gstInput.value;
                }
            }
            document.getElementById('combined_gstno').value = gstValue;
        }

        // Add an 'action' field to the form to tell the backend what to do
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('acchead');
            if (!form.querySelector('#action_type')) {
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.id = 'action_type';
                actionInput.name = 'action';
                actionInput.value = '';
                form.appendChild(actionInput);
            }
            
            // Client-side logic for the buttons
            document.getElementById('btnnew').addEventListener('click', function() {
                window.scrollTo(0, 0);

                const form = document.querySelector('form');
                if (form) {
                    form.reset();
                    document.getElementById('action_type').value = 'new';
                    document.getElementById('ncode').value = '0'; // Reset the hidden ID field to 0
                    // You might need to manually reset other fields here
                    document.getElementById('txtcreatedate').value = '<?php echo date('d-m-Y'); ?>';
                    document.getElementById('txtdate').value = '<?php echo date('d-m-Y'); ?>';
                }
            });

            document.getElementById('btnupdate').addEventListener('click', function() {
                combinePanGst(); // Combine values before submitting
                document.getElementById('action_type').value = 'modify';
                document.getElementById('acchead').submit();
            });

      document.getElementById('btndelete').addEventListener('click', function() {
    if (confirm('Are you sure you want to delete this record?')) {
        // Set the hidden 'action' field to 'delete'
        document.getElementById('action_type').value = 'delete';
        
        // The hidden 'ncode' field already holds the ID from the GET parameter
        // This is crucial. When you load the page with ?edit_id=X,
        // the PHP code populates <input type="hidden" id="ncode" name="ncode" value="X" />
        
        // Submit the form
        document.getElementById('acchead').submit();
    }
});
            document.getElementById('btnexit').addEventListener('click', function() {
                window.location.href = 'index.php';
            });
            
            document.getElementById('btnsave').addEventListener('click', function() {
                combinePanGst(); // Combine values before submitting
                document.getElementById('action_type').value = 'save';
            });
        });
    </script>
    
</head>

<body>
    <?php
    include 'header.php';
    ?>
    
    <div id="wrapper">
        <div class="section-heading">Account Head</div>
        
        <?php if ($access == 1) { ?>
        
        <form method="post" action="accounthead.php" id="acchead" enctype="multipart/form-data">
            <input type="hidden" id="ncode" name="ncode" value="<?php echo htmlspecialchars($ncodeValue); ?>" />
            <input type="hidden" id="oldcode" name="oldcode" value="<?php echo htmlspecialchars($editData['txtcode'] ?? '0'); ?>" />
            <input type="hidden" id="tranid" name="tranid" value="<?php echo htmlspecialchars($editData['tranid'] ?? '0'); ?>" />
            <input type="hidden" id="adcustid" name="adcustid" value="<?php echo htmlspecialchars($editData['adcustid'] ?? '0'); ?>" />
            <input type="hidden" id="adcompid" name="adcompid" value="<?php echo htmlspecialchars($editData['adcompid'] ?? '0'); ?>" />
            <input type="hidden" id="nncode" name="nncode" value="<?php echo htmlspecialchars($editData['nncode'] ?? '0'); ?>" />
            <input type="hidden" id="hdocdt" name="hdocdt" value="<?php echo htmlspecialchars($editData['hdocdt'] ?? ''); ?>" />
            <input type="hidden" id="empid" name="empid" value="<?php echo htmlspecialchars($editData['empid'] ?? '0'); ?>" />
            <input type="hidden" id="action_type" name="action" value="" />
            <input type="hidden" id="combined_panno" name="txtpanno">
            <input type="hidden" id="combined_gstno" name="txtgstno">


            <div class="form-section">
                <div class="form-container">
                    <div class="form-field">
                        <label for="groupcode" class="form-label">Group Code</label>
                        <div class="form-input">
                            <select class="select-input" id="groupcode" name="groupcode">
                                <option value=''>Select</option>
                                <option value='Liability' <?php if (isset($groupcode) && $groupcode == 'Liability') echo 'selected'; ?>>Liability</option>
                                <option value='Asset' <?php if (isset($groupcode) && $groupcode == 'Asset') echo 'selected'; ?>>Asset</option>
                                <option value='Profit & Loss' <?php if (isset($groupcode) && $groupcode == 'Profit & Loss') echo 'selected'; ?>>Profit & Loss</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="level1" class="form-label">Level 1</label>
                        <div class="form-input">
                            <select class="select-input" id="level1" name="level1">
                                <option value=''>Select</option>
                                <option value='Unsecured Loans' <?php if (isset($level1) && $level1 == 'Unsecured Loans') echo 'selected'; ?>>Unsecured Loans</option>
                                <option value='Deferred Tax' <?php if (isset($level1) && $level1 == 'Deferred Tax') echo 'selected'; ?>>Deferred Tax</option>
                                <option value='Fixed Assets' <?php if (isset($level1) && $level1 == 'Fixed Assets') echo 'selected'; ?>>Fixed Assets</option>
                                <option value='Capital work in progress' <?php if (isset($level1) && $level1 == 'Capital work in progress') echo 'selected'; ?>>Capital work in progress</option>
                                <option value='Depreciation Reserve' <?php if (isset($level1) && $level1 == 'Depreciation Reserve') echo 'selected'; ?>>Depreciation Reserve</option>
                                <option value='Investment' <?php if (isset($level1) && $level1 == 'Investment') echo 'selected'; ?>>Investment</option>
                                <option value='Sundry Creditors' <?php if (isset($level1) && $level1 == 'Sundry Creditors') echo 'selected'; ?>>Sundry Creditors</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="cmbcontrolhead" class="form-label">Control Head</label>
                        <div class="form-input">
                            <select class="select-input" id="cmbcontrolhead" name="cmbcontrolhead" onchange="setvalues();">
                                <option value=''>Select</option>
                                <option value='SU' <?php if (isset($cmbcontrolhead) && $cmbcontrolhead == 'SU') echo 'selected'; ?>>SU</option>
                                <option value='AD' <?php if (isset($cmbcontrolhead) && $cmbcontrolhead == 'AD') echo 'selected'; ?>>AD</option>
                                <option value='CU' <?php if (isset($cmbcontrolhead) && $cmbcontrolhead == 'CU') echo 'selected'; ?>>CU</option>
                                <option value='GE' <?php if (isset($cmbcontrolhead) && $cmbcontrolhead == 'GE') echo 'selected'; ?>>GE</option>
                                <option value='EM' <?php if (isset($cmbcontrolhead) && $cmbcontrolhead == 'EM') echo 'selected'; ?>>EM</option>
                                <option value='TL' <?php if (isset($cmbcontrolhead) && $cmbcontrolhead == 'TL') echo 'selected'; ?>>TL</option>
                                <option value='SH' <?php if (isset($cmbcontrolhead) && $cmbcontrolhead == 'SH') echo 'selected'; ?>>SH</option>
                            </select>
                            <input type="checkbox" id="adchk" name="adchk" title="Click for AD Entry">
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="txtinitials1" class="form-label">Initials</label>
                        <div class="form-input icon-wrapper">
                            <input type="text" class="text-input" name="txtinitials1" id="txtinitials1" maxlength="100" value="<?php echo htmlspecialchars($txtinitials1 ?? ''); ?>">
                            <img border="0" id="searching" alt="Search" src="./zoom.png" />
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="txtcode" class="form-label">Code</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtcode" id="txtcode" readonly value="<?php echo htmlspecialchars($txtcode ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtdesc" class="form-label">Trade Name</label>
                        <div class="form-input">
                            <input type="text" class="text-input" name="txtdesc" id="txtdesc" onkeydown="concurrententry();" value="<?php echo htmlspecialchars($txtdesc ?? ''); ?>" />
                            <input type="checkbox" name="searchbox" id="searchbox">Search
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="txtcreatedate" class="form-label">Creation Date</label>
                        <div class="form-input icon-wrapper">
                            <input type="text" class="text-input" name="txtcreatedate" id="txtcreatedate" value="<?php echo htmlspecialchars($creationDateValue); ?>" />
                            <a href="javascript:NewCal('txtcreatedate','DDMMYYYY')"><img border="0" src="./calendar.png" alt="Pick a date" /></a>
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="txtopnbal" class="form-label">Opening Balance</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtopnbal" id="txtopnbal" value="<?php echo htmlspecialchars($txtopnbal ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtyropnbal" class="form-label">Yearly Opening Balance</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtyropnbal" id="txtyropnbal" value="<?php echo htmlspecialchars($txtyropnbal ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtfcopnbal" class="form-label">Foreign Currency Opn. Bal.</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtfcopnbal" id="txtfcopnbal" value="<?php echo htmlspecialchars($txtfcopnbal ?? ''); ?>" /></div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-container">
                    <div class="form-field">
                        <label for="txtname" class="form-label">Legal Name</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtname" id="txtname" value="<?php echo htmlspecialchars($txtname ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtshipaddr1" class="form-label">Shipping Address 1</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtshipaddr1" id="txtshipaddr1" value="<?php echo htmlspecialchars($txtshipaddr1 ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtaddress1" class="form-label">Address 1</label>
                        <div class="form-input">
                            <input type="text" class="text-input" name="txtaddress1" id="txtaddress1" value="<?php echo htmlspecialchars($txtaddress1 ?? ''); ?>" />
                            <input type="checkbox" name="cp" id="cp" onclick="copydata();">copy
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="txtshipaddr2" class="form-label">Shipping Address 2</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtshipaddr2" id="txtshipaddr2" value="<?php echo htmlspecialchars($txtshipaddr2 ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtaddress2" class="form-label">Address 2</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtaddress2" id="txtaddress2" value="<?php echo htmlspecialchars($txtaddress2 ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtshipaddr3" class="form-label">Shipping Address 3</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtshipaddr3" id="txtshipaddr3" value="<?php echo htmlspecialchars($txtshipaddr3 ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtaddress3" class="form-label">Address 3</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtaddress3" id="txtaddress3" value="<?php echo htmlspecialchars($txtaddress3 ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtshipaddr4" class="form-label">Shipping Address 4</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtshipaddr4" id="txtshipaddr4" value="<?php echo htmlspecialchars($txtshipaddr4 ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtaddress4" class="form-label">Address 4</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtaddress4" id="txtaddress4" value="<?php echo htmlspecialchars($txtaddress4 ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtfax" class="form-label">Fax</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtfax" id="txtfax" value="<?php echo htmlspecialchars($txtfax ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtplacestate2" class="form-label">Place/State</label>
                        <div class="form-input">
                            <select class="select-input" name="txtplacestate2" id="txtplacestate2" onchange="setgstno()">
                                <option value=''>Select</option>
                                <option value='Andaman & Nicobar' <?php if (isset($txtplacestate2) && $txtplacestate2 == 'Andaman & Nicobar') echo 'selected'; ?>>Andaman & Nicobar</option>
                                <option value='Maharastra' <?php if (isset($txtplacestate2) && $txtplacestate2 == 'Maharastra') echo 'selected'; ?>>Maharastra</option>
                                <option value='Karnataka' <?php if (isset($txtplacestate2) && $txtplacestate2 == 'Karnataka') echo 'selected'; ?>>Karnataka</option>
                                <option value='Tamil Nadu' <?php if (isset($txtplacestate2) && $txtplacestate2 == 'Tamil Nadu') echo 'selected'; ?>>Tamil Nadu</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="txtcontactper" class="form-label">Contact Person</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtcontactper" id="txtcontactper" value="<?php echo htmlspecialchars($txtcontactper ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtpinno" class="form-label">Pin Code</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtpinno" id="txtpinno" maxlength="6" value="<?php echo htmlspecialchars($txtpinno ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtcommercialcontper" class="form-label">Commercial Cont. Person</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtcommercialcontper" id="txtcommercialcontper" value="<?php echo htmlspecialchars($txtcommercialcontper ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtphone" class="form-label">Phone No</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtphone" id="txtphone" value="<?php echo htmlspecialchars($txtphone ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtgstcontact" class="form-label">GST Contact Person</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtgstcontact" id="txtgstcontact" value="<?php echo htmlspecialchars($txtgstcontact ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtemail" class="form-label">Email</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtemail" id="txtemail" value="<?php echo htmlspecialchars($txtemail ?? ''); ?>" /></div>
                    </div>



                   
                    <div class="form-field">
                        <label for="txtgstrate" class="form-label">GST Date</label>
                        <div class="form-input icon-wrapper">
                            <input type="text" class="text-input" name="txtgstrate" id="txtgstrate" value="<?php echo htmlspecialchars($txtgstrate ?? ''); ?>" />
                            <a href="javascript:NewCal('txtgstrate','DDMMYYYY')"><img border="0" src="./calendar.png" alt="Pick a date" /></a>
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="txtgsttype" class="form-label">GST Type</label>
                        <div class="form-input">
                            <select class="select-input" id="txtgsttype" name="txtgsttype">
                                <option value=''>Select</option>
                                <option value='Regular' <?php if (isset($txtgsttype) && $txtgsttype == 'Regular') echo 'selected'; ?>>Regular</option>
                                <option value='Composite' <?php if (isset($txtgsttype) && $txtgsttype == 'Composite') echo 'selected'; ?>>Composite</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-field full-width">
                        <label class="form-label">Active</label>
                        <div class="form-input">
                            <input type="checkbox" id="activeb" name="activeb" value="t" <?php if (isset($activeb) && $activeb == 't') echo 'checked'; ?>>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-container">
                    <div class="form-field">
                        <label for="txtrcno" class="form-label">R.C No</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtrcno" id="txtrcno" value="<?php echo htmlspecialchars($txtrcno ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtcstno" class="form-label">CST No</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtcstno" id="txtcstno" value="<?php echo htmlspecialchars($txtcstno ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtrange" class="form-label">Range</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtrange" id="txtrange" value="<?php echo htmlspecialchars($txtrange ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txtdate" class="form-label">Date</label>
                        <div class="form-input icon-wrapper">
                            <input type="text" class="text-input" name="txtdate" id="txtdate" value="<?php echo htmlspecialchars($txtDateValue); ?>" />
                            <a href="javascript:NewCal('txtdate','DDMMYYYY')"><img border="0" src="./calendar.png" alt="Pick a date" /></a>
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="txtdivision" class="form-label">Division</label>
                        <div class="form-input"><input type="text" class="text-input" name="txtdivision" id="txtdivision" value="<?php echo htmlspecialchars($txtdivision ?? ''); ?>" /></div>
                    </div>
                    <div class="form-field">
                        <label for="txttype" class="form-label">Type</label>
                        <div class="form-input"><input type="text" class="text-input" name="txttype" id="txttype" value="<?php echo htmlspecialchars($txttype ?? ''); ?>" /></div>
                    </div>
                </div>
            </div>


            <!-- ✅ PAN & GST Section -->
<div class="form-section">
    <div class="form-container">
        
        <!-- PAN Number -->
        <div class="form-field full-width" style="display: flex; align-items: center; gap: 20px;">
    <!-- PAN No. Field -->
    <!-- <div style="flex: 1;"> -->
        <label class="form-label">PAN No.</label>
        <div class="form-input pan-gst-wrapper">
            <?php 
            $panValue = isset($txtpanno) ? str_split($txtpanno) : [];
            for ($i = 0; $i <= 9; $i++) {
                $val = isset($panValue[$i]) ? htmlspecialchars($panValue[$i]) : '';
                echo "<input type='text' maxlength='1' id='pan$i' class='gst-box' value='$val' 
                oninput=\"focusNextInput(this,'pan',$i,9)\" />";
            }
            ?>
        </div>
    <!-- </div> -->

    <!-- Percentage Field -->
    <div style="width: 450px;">
        <label class="form-label">Percentage</label>
        <input type="number" class="form-input" placeholder="%" min="0" max="100" step="0.01" />
    </div>
</div>


        <!-- GST Number -->
        <div class="form-field full-width">
            <label class="form-label">GST No.</label>
            <div class="form-input pan-gst-wrapper">
                <?php 
                $gstValue = isset($txtgstno) ? str_split($txtgstno) : [];
                for ($i = 0; $i <= 14; $i++) {
                    $val = isset($gstValue[$i]) ? htmlspecialchars($gstValue[$i]) : '';
                    echo "<input type='text' maxlength='1' id='gst$i' class='gst-box' value='$val' oninput=\"focusNextInput(this,'gst',$i,14)\" />";
                }
                ?>
            </div>
        </div>

    
<div class="form-field full-width" style="text-align:center; margin-top:10px;">
    <button type="button" id="btnBank" class="btn">+ Add Bank</button>
</div>

<div id="bankForm" class="bank-section" style="display:none;">
    <h3>Bank Details</h3>
    <div class="bank-details-grid">
        <div class="form-field">
            <label class="form-label">Bank Name</label>
            <div class="form-input">
                <input type="text" name="bank_name" class="text-input" value="<?php echo htmlspecialchars($editData['bank_name'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-field">
            <label class="form-label">Account No.</label>
            <div class="form-input">
                <input type="text" name="account_no" class="text-input" value="<?php echo htmlspecialchars($editData['account_no'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-field">
            <label class="form-label">IFSC Code</label>
            <div class="form-input">
                <input type="text" name="ifsc_code" class="text-input" value="<?php echo htmlspecialchars($editData['ifsc_code'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-field">
            <label class="form-label">Branch</label>
            <div class="form-input">
                <input type="text" name="branch" class="text-input" value="<?php echo htmlspecialchars($editData['branch'] ?? ''); ?>">
            </div>
        </div>
    </div>
</div>
</div>

<script>
    // ✅ Toggle Bank Form
    document.addEventListener("DOMContentLoaded", function() {
        const bankBtn = document.getElementById("btnBank");
        const bankForm = document.getElementById("bankForm");
        bankBtn.addEventListener("click", function() {
            bankForm.style.display = (bankForm.style.display === "none") ? "block" : "none";
        });
    });
</script>


            <div class="button-group">
                <input type="button" id="btnnew" class="btn" value="New">
                <input type="submit" id="btnsave" class="btn" value="Save">
                <input type="button" id="btnupdate" class="btn" value="Modify">
                <input type="button" id="btndelete" class="btn" value="Delete">
                <input type="button" id="btnexit" class="btn" value="Exit">
            </div>
            
            <?php if (!empty($message)) { ?>
                <div style="text-align: center; margin-top: 10px; color: green;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php } ?>

        </form>
        
        <?php } else { ?>
            <p>You do not have access to this page.</p>
        <?php } ?>
    </div>
</body>
</html>