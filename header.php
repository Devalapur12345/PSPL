<?php
// Main menu tabs
$menuTabs = ["Masters", "Transactions", "Reports", "Letters", "Utilities"];

// Category map (1st level)
$categoryMap = [
    "Masters"      => ["Masters"],
    "Transactions" => ["Transactions"],
    "Reports"      => ["Reports"],
    "Letters"      => ["Letters"],
    "Utilities"    => ["Utilities"],
];

// Submenu map (2nd level)
$subMenuMap = [
    "Masters" => [
        "Masters" => [
            "Account Head", "Register Master", "Sub Register Master", "Employee Master",
            "Asst Group Head", "HSN Master", "Service Master", "State Master",
            "Department Master", "Designation Master", "TCS Master",
            "Vendore Reconcliliation", "Change Password", "Advance Ledger",
            "Check List", "Check List Master", "Active?InActive List", "GSTR2B/IMS"
        ],
    ],

    "Transactions" => [
        "Transactions" => [
            "Purchase To Payment","Bill Master","Transaction","GST Bill Passing",
            "Multiple GST Bill Passing","View Transactions","View All Transactions",
            "Bank Reconciliation","GST Pr Transaction","Import Transaction","Expenses Register",
            "Misc. Transactions","DBN User Entry","CRN User Entry","JV User Entry",
            "JV Entry Modify","CRN SU Entry","DBN CU Entry","EBRC Entry"
        ],
    ],

    "Reports" => [
        "Reports" => [
            "Bank Receipt Report","View Transaction Stores","Cash/Bank Book","GRN Register",
            "GST Register","Misc. Reports","Schedule & Trail Bal","Paid Voucher Report",
            "Pending Payments","Account Ledger","Printing","Register","Sales Register",
            "Other Reports","Closing Stock Report","Pending Orders","Open Indent","Open PO Reports"
        ]
    ],

    "Letters" => [
        "Letters" => ["SSI Confirmation Letter","MSME Letter","Reminder Letter Register","Balance Confirmation Letter"],
    ],

    "Utilities" => [
        "Utilities" => [
            "Pending Pay Patch","Software Status","Transaction","Employee","Bank Details",
            "Customer Address Label","Leave Card","Driving License Expiry Date Alert",
            "Invoice","View","Orders","Screen Authorization","NCT","New Account Ledger Created"
        ],
    ],
];

// Sub-submenu map (3rd level)
$subSubMenuMap = [
    // Transactions
    "Purchase To Payment" => ["With Passbook", "Without Passbook"],
    "Deposit"             => ["Cash Deposit", "Cheque Deposit"],
    "Withdrawal"          => ["Cash Withdrawal", "Slip Withdrawal"],
    "Open RD"             => ["New RD", "Joint RD"],
    "FD Renewal"          => ["Renew with Interest", "Renew Principal Only"],

    // Loan-like examples
    "Loan Application"    => ["New Application", "Pending Approval", "Approved Loans"],
    "Loan Reports"        => ["Daily Loan Report", "Monthly Loan Report"],
    "Apply Term Loan"     => ["Short Term", "Long Term"],
    "CC Transactions"     => ["Overdraft", "Limit Enhancement"],
    "Apply OD"            => ["Temporary OD", "Permanent OD"]
];

/**
 * ===== Link helpers (fix for "line 217 not going to proper destination") =====
 * We generate a link per item based on tab + item title, with optional overrides.
 */

function slugify($str) {
    $s = strtolower(trim($str));
    $s = preg_replace('/[^a-z0-9]+/i', '-', $s);
    return trim($s, '-');
}
function baseForTab($tab) {
    return match ($tab) {
        "Masters"      => "/masters/",
        "Transactions" => "/transactions/",
        "Reports"      => "/reports/",
        "Letters"      => "/letters/",
        "Utilities"    => "/utilities/",
        default        => "/",
    };
}

// Explicit overrides for 2nd-level items (use these if your filenames differ)
$linkOverrides = [
    "Account Head"       => "./accountHead.php",
    "Account Ledger"     => "./view_accounts.php"
    // Add more one-offs here if needed:
    // "Register Master" => "/masters/registerMaster.php",
];

// Explicit overrides for 3rd-level (by parent -> child)
$thirdOverrides = [
    "Purchase To Payment" => [
        "With Passbook"    => "/transactions/purchase-to-payment/with-passbook.php",
        "Without Passbook" => "/transactions/purchase-to-payment/without-passbook.php",
    ],
    // Add more one-offs here if needed:
    // "Loan Application" => ["New Application" => "/loans/application/new.php"]
];

function linkForItem($tab, $item) {
    global $linkOverrides;
    if (isset($linkOverrides[$item])) return $linkOverrides[$item];
    // Default: /{tab-slug}/{item-slug}.php
    $base = baseForTab($tab);
    return $base . slugify($item) . ".php";
}

function linkForThird($tab, $parentItem, $subItem) {
    global $thirdOverrides;
    if (isset($thirdOverrides[$parentItem][$subItem])) return $thirdOverrides[$parentItem][$subItem];
    // Default: /{tab-slug}/{parent-slug}/{child-slug}.php
    $base   = baseForTab($tab);
    $parent = slugify($parentItem);
    $child  = slugify($subItem);
    return $base . $parent . "/" . $child . ".php";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Mega Menu PHP + Bootstrap</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <style>
    /* Mega menu custom styles */
    .mega-dropdown {
      position: absolute;
      width: 1000px;
      left: 0;
      top: 100%;
      background: #fff;
      border: 1px solid #ddd;
      display: none;
      z-index: 1000;
    }
    .nav-item:hover .mega-dropdown {
      display: flex;
    }
    .nav-link {
      display: inline-block;
      padding: 6px 12px;
      color: #ffffff !important;
      transition: all 0.2s ease;
    }
    .nav-link:hover {
      background-color: #fff !important;
      color: #000 !important;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    .mega-left {
      width: 250px;
      background: #f8f9fc;
      border-right: 1px solid #ddd;
    }
    .mega-left .active {
      background: #fff;
      font-weight: bold;
    }
    .mega-right {
      flex: 1;
      padding: 15px;
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      position: relative;
      overflow: visible;
    }
    .submenu h6 {
      margin: 0 0 6px 0;
    }

    /* 3rd level (sub-submenu) */
    .has-sub-submenu { position: relative; }
    .sub-submenu {
      position: absolute;
      top: 0;
      left: 100%;
      background: #fff;
      border: 1px solid #ddd;
      width: 220px;
      padding: 10px;
      box-shadow: 2px 2px 5px rgba(0,0,0,0.1);
      display: none;
      z-index: 1001;
    }
    .sub-submenu a {
      display: block;
      padding: 4px 6px;
      color: #666;
      text-decoration: none;
      border-radius: 4px;
    }
    .sub-submenu a:hover {
      color: #000;
      text-decoration: none;
      background: #f2f2f2;
    }
    .cat-item i { transition: transform 0.3s ease; }
    .cat-item:hover i { transform: rotate(180deg); }

    .category-item i {
      opacity: 0;
      transition: all 0.3s ease;
    }
    .category-item:hover i { opacity: 1; }

    .has-sub-submenu .sub-submenu-arrow {
      transition: opacity 0.3s ease;
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      opacity: 0;
    }
    .has-sub-submenu:hover .sub-submenu-arrow { opacity: 1; }
    .has-sub-submenu:hover .sub-submenu { display: block; }

    .form-control::placeholder { color: #D9D9D9 !important; opacity: 1; }
  </style>
</head>
<body>
<div class="container-fluid p-0">
  <div class="d-flex justify-content-between align-items-center px-4 bg-black text-white fw-bold" style="height:40px; max-width:80%; position:relative; right:-20%;">
    <div>ERP Version 1.25.4</div>
    <div class="d-flex gap-3 align-items-center">
      <span>ACCOUNTS</span>
      <span>19-AUG-2025</span>
      <span>ADMIN</span>
      <a href="/change-pass.php" class="text-white text-decoration-none">Change Password</a>
      <a href="/auth-form.php" class="text-white text-decoration-none">Sign in / Sign Up</a>
    </div>
  </div>

  <nav class="navbar navbar-expand-lg navbar-dark" style="background:#212121;height:56px;">
    <div class="container-fluid">
      <a class="navbar-brand" href="/PSPL.png">
        <img src="PSPL.png" height="40" alt="Logo">
      </a>

      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php foreach ($menuTabs as $tab): ?>
          <li class="nav-item position-relative">
            <a class="nav-link fw-bold px-3 cat-item" href="#"><?= $tab ?> <i class="fa-solid fa-chevron-down small"></i></a>

            <?php if (isset($categoryMap[$tab])): ?>
              <div class="mega-dropdown shadow">
                <!-- Left Categories -->
                <div class="mega-left">
                  <?php foreach ($categoryMap[$tab] as $cat): ?>
                    <div class="p-2 border-bottom category-item" data-target="cat-<?= md5($cat) ?>">
                      <?= $cat ?> <i class="fa-solid fa-chevron-right float-end"></i>
                    </div>
                  <?php endforeach; ?>
                </div>

                <!-- Right Panels -->
                <div class="mega-right">
                  <?php foreach ($categoryMap[$tab] as $cat): ?>
                    <div id="cat-<?= md5($cat) ?>" class="submenu d-none">
                      <?php if (isset($subMenuMap[$cat]) && is_array($subMenuMap[$cat])): ?>
                        <?php foreach ($subMenuMap[$cat] as $section => $items): ?>
                          <h6 class="text-muted fw-bold"><?= $section ?></h6>

                          <?php foreach ($items as $item): ?>
                            <div class="py-1 position-relative has-sub-submenu">
                              <!-- FIX: dynamic link instead of hard-coded accountHead.php -->
                              <a
                                href="<?= htmlspecialchars(linkForItem($tab, $item)) ?>"
                                class="text-dark text-decoration-none"
                              >
                                <?= $item ?>
                              </a>

                              <?php if (isset($subSubMenuMap[$item])): ?>
                                <span class="sub-submenu-arrow"><i class="fa-solid fa-chevron-right small"></i></span>
                                <div class="sub-submenu">
                                  <?php foreach ($subSubMenuMap[$item] as $subItem): ?>
                                    <a href="<?= htmlspecialchars(linkForThird($tab, $item, $subItem)) ?>">
                                      <?= $subItem ?>
                                    </a>
                                  <?php endforeach; ?>
                                </div>
                              <?php endif; ?>
                            </div>
                          <?php endforeach; ?>

                        <?php endforeach; ?>
                      <?php else: ?>
                        <span class="text-danger">No submenu available</span>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="position-absolute" style="right: 40px; width: 700px;">
        <form class="d-flex align-items-center rounded overflow-hidden" style="background:#404040; height:40px; width:700px;">
          <span class="px-3 text-secondary d-flex align-items-center">
            <i class="fa fa-search"></i>
          </span>
          <input type="text" class="form-control border-0 bg-transparent text-white" placeholder="Search for Products" style="box-shadow:none;">
          <span class="px-3 text-secondary d-flex align-items-center cursor-pointer">
            <i class="fa fa-microphone"></i>
          </span>
        </form>
      </div>
    </div>
  </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Switch right panel based on left category hover
  document.querySelectorAll('.category-item').forEach(cat => {
    cat.addEventListener('mouseenter', function () {
      document.querySelectorAll('.submenu').forEach(sm => sm.classList.add('d-none'));
      const target = document.getElementById(this.dataset.target);
      if (target) target.classList.remove('d-none');

      this.parentNode.querySelectorAll('.category-item').forEach(c => c.classList.remove('active'));
      this.classList.add('active');
    });
  });
</script>
</body>
</html>
