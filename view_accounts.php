<?php
// Start a session
if (!isset($_SESSION)) {
    session_start();
}
// Include the database connection
require_once 'dbconnPostgres.php';

// Initialize variables
$message = '';
$accounts = [];
$company_name = "POLYHYDRON SYSTEMS PVT. LTD.";

// ** NEW: Handle DELETE request **
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    
    // Check if the ID is valid
    if ($deleteId > 0) {
        $sql_delete = "DELETE FROM account_heads WHERE ncode = $1";
        $result_delete = pg_query_params($conn, $sql_delete, array($deleteId));
        
        if ($result_delete) {
            $message = "Record with ID " . htmlspecialchars($deleteId) . " deleted successfully.";
        } else {
            $message = "Error deleting record: " . pg_last_error($conn);
        }
    } else {
        $message = "Invalid record ID for deletion.";
    }
    
    // Redirect back to the view page after deletion attempt
    header("Location: view_accounts.php?show=all&message=" . urlencode($message));
    exit();
}

// Get the last transaction date from the 'account_heads' table
$sql_last_date = "SELECT MAX(txtcreatedate) as last_date FROM account_heads";
$result_last_date = pg_query($conn, $sql_last_date);

if ($result_last_date) {
    $row = pg_fetch_assoc($result_last_date);
    if ($row['last_date']) {
        $last_date_obj = new DateTime($row['last_date']);
        $to_date = $last_date_obj->format('d-m-Y');
    } else {
        // Fallback to today's date if no records are found
        $to_date = date('d-m-Y');
    }
} else {
    $message = "Error fetching last transaction date: " . pg_last_error($conn);
    // Fallback to today's date on query failure
    $to_date = date('d-m-Y');
}

// Set the from_date (this is static in your original code)
$from_date = "01-04-2025";

// Only fetch records from the database if the 'show=all' parameter is in the URL.
if (isset($_GET['show']) && $_GET['show'] === 'all') {
    // Select from 'account_heads' and include 'ncode' to identify records
    $sql_select = "SELECT ncode, txtcreatedate, txtcode, txtdesc FROM account_heads ORDER BY txtcreatedate ASC";
    $result_select = pg_query($conn, $sql_select);

    if ($result_select) {
        $fetched_data = pg_fetch_all($result_select);
        $accounts = ($fetched_data !== false) ? $fetched_data : [];
    } else {
        $message = "Error fetching data: " . pg_last_error($conn);
    }
}

// Display messages from redirects
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}

pg_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Ledger</title>
    <link rel="SHORTCUT ICON" HREF="images/logo.png">
    
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f0f2f5;
        }
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ccc;
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .btn {
            background-color: #e9e9e9;
            color: #333;
            padding: 8px 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            text-decoration: none;
        }
        .btn-danger {
            background-color: #f44336;
            color: white;
            border: 1px solid #d32f2f;
        }
        .btn:hover {
            background-color: #dcdcdc;
        }
        .btn-danger:hover {
            background-color: #d32f2f;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-header h1 {
            margin: 0;
            font-size: 1.5em;
        }
        .report-header p {
            margin: 5px 0 0 0;
            font-size: 1em;
        }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9em;
        }
        .ledger-table th, .ledger-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        .ledger-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .ledger-table td:nth-child(8), /* Debit */
        .ledger-table td:nth-child(9), /* Credit */
        .ledger-table td:nth-child(10), /* Balance */
        .ledger-table td:nth-child(14) { /* GST Amount */
            text-align: right;
        }
        .ledger-table tfoot td {
            font-weight: bold;
        }
        /* Styles for printing */
        @media print {
            body {
                background-color: #fff;
                margin: 0;
            }
            .page-container {
                border: none;
                box-shadow: none;
                margin: 0;
                max-width: 100%;
            }
            .toolbar {
                display: none; /* Hide buttons when printing */
            }
            .action-column {
                display: none; /* Hide the action column when printing */
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="toolbar">
            <div>
                <a href="view_accounts.php" class="btn">Back/Clear</a>
                <a href="view_accounts.php?show=all" class="btn">View Report</a>
                <button onclick="exportTableToExcel('ledgerTable', 'ledger-report')" class="btn">Export to Excel</button>
                <button onclick="window.print()" class="btn">Print</button>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if (isset($_GET['show']) && $_GET['show'] === 'all'): ?>
            <div class="report-header">
                <h1><?php echo htmlspecialchars($company_name); ?></h1>
                <p>Ledger Balance from <?php echo htmlspecialchars($from_date); ?> to <?php echo htmlspecialchars($to_date); ?></p>
            </div>

            <table class="ledger-table" id="ledgerTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Doc No</th>
                        <th>GRN No</th>
                        <th>GRN Date</th>
                        <th>Bill No</th>
                        <th>Bill Date</th>
                        <th>Particulars</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Balance</th>
                        <th>Realisation</th>
                        <th>Days</th>
                        <th>GSTR-2</th>
                        <th>GST Amount</th>
                        <th class="action-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($from_date); ?></td>
                        <td colspan="6"><strong>Opening Balance</strong></td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td colspan="4"></td>
                        <td class="action-column"></td>
                    </tr>
                    <tr>
                         <td colspan="2"><strong>Ledger Code</strong></td>
                         <td><strong>GE-IT01</strong></td>
                         <td colspan="12"></td>
                    </tr>
                    
                    <?php 
                        $total_debit = 0.00;
                        $total_credit = 0.00;
                        if (!empty($accounts)):
                            foreach ($accounts as $account):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($account['txtcreatedate']); ?></td>
                        <td><?php echo htmlspecialchars($account['txtcode']); ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo htmlspecialchars($account['txtdesc']); ?></td>
                        <td>1500.00</td>
                        <td>0.00</td>
                        <td>1500.00</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>0.00</td>
                        <td class="action-column">
                            <a href="accounthead.php?edit_id=<?php echo htmlspecialchars($account['ncode']); ?>" class="btn">Edit</a>
                            <button onclick="confirmDelete(<?php echo htmlspecialchars($account['ncode']); ?>)" class="btn btn-danger">Delete</button>
                        </td>
                    </tr>
                    <?php 
                            endforeach;
                        else:
                    ?>
                    <tr>
                        <td colspan="15" style="text-align: center;">No transactions found for this period.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6"></td>
                        <td><strong>Last Reconciliation Date:</strong></td>
                        <td colspan="2"></td>
                        <td></td>
                        <td colspan="4"></td>
                        <td class="action-column"></td>
                    </tr>
                    <tr>
                        <td colspan="6"></td>
                        <td><strong>Total</strong></td>
                        <td style="text-align: right;"><strong><?php echo number_format($total_debit, 2); ?></strong></td>
                        <td style="text-align: right;"><strong><?php echo number_format($total_credit, 2); ?></strong></td>
                        <td colspan="4"></td>
                        <td style="text-align: right;"><strong>0</strong></td>
                        <td class="action-column"></td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>
    </div>

    <script>
    function exportTableToExcel(tableID, filename = ''){
        var downloadLink;
        var dataType = 'application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);
        var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
        
        filename = filename?filename+'.xls':'excel_data.xls';
        
        downloadLink = document.createElement("a");
        
        document.body.appendChild(downloadLink);
        
        if(navigator.msSaveOrOpenBlob){
            var blob = new Blob(['\ufeff', tableHTML], {
                type: dataType
            });
            navigator.msSaveOrOpenBlob( blob, filename);
        }else{
            downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
            downloadLink.download = filename;
            downloadLink.click();
        }
    }
    
    // ** New JavaScript function for deletion confirmation **
    function confirmDelete(id) {
        if (confirm("Are you sure you want to delete this record? This action cannot be undone.")) {
            // Redirect to the same page with the delete_id parameter
            window.location.href = "view_accounts.php?delete_id=" + id;
        }
    }
    </script>
</body>
</html>