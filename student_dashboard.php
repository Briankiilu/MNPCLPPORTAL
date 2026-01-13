<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

require_once 'dbconnect.php'; 

$username = htmlspecialchars($_SESSION['username'] ?? ''); 
$role = htmlspecialchars($_SESSION['user_role'] ?? ''); 

// Initialize variables
$first_name = $_SESSION['first_name'] ?? '';
$last_name = $_SESSION['last_name'] ?? ''; 
$student_adm = null; 
$profile_picture_path = '../images/default-profile.png'; 

if (isset($connection) && !empty($username)) {
    
    $safe_username = mysqli_real_escape_string($connection, $username);
    
    // UPDATED QUERY: Now fetching first_name and full_name explicitly
    $query = "SELECT profile_pic_path, adm_no, first_name, full_name FROM trainees WHERE username = '$safe_username' LIMIT 1"; 
    $result = mysqli_query($connection, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        $db_path = $row['profile_pic_path'];
        $student_adm = $row['adm_no']; 
        $_SESSION['student_adm'] = $student_adm; 

        // NAME LOGIC: Ensure First Name is available
        if (!empty($row['first_name'])) {
            $first_name = $row['first_name'];
        } elseif (!empty($row['full_name'])) {
            // Extract first name if the specific column is empty
            $name_parts = explode(' ', trim($row['full_name']));
            $first_name = $name_parts[0];
        } else {
            $first_name = "Trainee";
        }
        
        // Update Session
        $_SESSION['first_name'] = $first_name;

        if (!empty($db_path) && file_exists($db_path)) {
            $_SESSION['profile_pic'] = $db_path;
        } else {
             $_SESSION['profile_pic'] = '../images/default-profile.png';
        }
    }
} else {
    $_SESSION['profile_pic'] = '../images/default-profile.png';
}

$profile_picture_path = htmlspecialchars($_SESSION['profile_pic']);
$current_view = $_GET['view'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | MNP CLP Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
       
        :root {
            --mnp-blue: #007bff; 
            --mnp-dark-blue: #0056b3;
            --sidebar-text-color: black;
            --sidebar-hover-bg: rgba(255, 255, 255, 0.2); 
            --sidebar-active-bg: #fff; 
            --sidebar-active-text: var(--mnp-blue); 
            --light-gray-bg: #f4f4f4;
            --white: #fff;
            --shadow-light: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--light-gray-bg);
            margin: 0;
            padding: 0;
            display: flex; 
            min-height: 10vh; 
        }

        .sidebar {
            width: 250px; 
            background-color: var(--mnp-blue);
            color: var(--sidebar-text-color);
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
            flex-shrink: 0; 
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 20px; 
            border-bottom: 1px solid rgba(0, 0, 0, 0.1); 
            margin-bottom: 20px;
            background:white;
        }

        .sidebar-header .logo {
            width: 90px;
            height: 90px; 
            margin-right: 15px; 
            border-radius: 5px; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); 
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 1.8em;
            font-weight: bold;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--sidebar-text-color);
            text-decoration: none;
            font-size: 1.05em;
            transition: background-color 0.2s, color 0.2s;
            border-radius: 0 50px 50px 0;
        }

        .sidebar-menu a i {
            margin-right: 15px;
            font-size: 1.2em;
        }

        .sidebar-menu a:hover {
            background-color: var(--sidebar-hover-bg);
        }

        .sidebar-menu .active a {
            background-color: var(--sidebar-active-bg);
            color: var(--sidebar-active-text);
            font-weight: bold;
        }

        .sidebar-menu .category-title {
            color: gold;
            font-size: 0.85em;
            text-transform: uppercase;
            padding: 15px 20px 5px 20px;
            margin-top: 15px;
            font-weight: bold;
        }
        
        .sidebar-menu .with-dropdown {
            justify-content: space-between;
        }
        .sidebar-menu .with-dropdown .fa-chevron-down {
            font-size: 0.8em;
            margin-right: 0;
        }

        .main-content {
            flex-grow: 1; 
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start; 
            margin-bottom: 20px;
            width: 100%;
        }

        .top-bar { 
            display: none;
        }
        
        .welcome-box {
            flex-grow: 1; 
            margin-right: 20px; 
            max-width: 80%; 
            background-color: var(--white);
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow-light);
            border-left: 5px solid var(--mnp-blue);
        }
        .welcome-box h2 {
            color: green;
            margin-top: 0;
            font-size: 1.8em;
            margin-bottom: 5px;
        }
        .welcome-box p {
            color: black;
            font-size: 1em;
        }
        
        .profile-box {
            display: flex;
            flex-direction: column;
            align-items: center; 
            min-width: 150px; 
            padding: 10px;
        }
        .profile-details {
            display: flex;
            flex-direction: column; 
            align-items: center; 
            margin-bottom: 5px; 
            position: relative; 
        }
        
        .profile-picture {
            width: 60px; 
            height: 60px;
            border-radius: 50%; 
            object-fit: cover; 
            border: 2px solid var(--mnp-blue);
            margin-bottom: 5px; 
        }

        .name-role {
            display: flex;
            flex-direction: column;
            text-align: center; 
            font-weight: bold;
            margin-bottom: 5px;
        }
        .user-name {
            font-size: 1.1em;
            color: #333;
        }
        .user-role {
            font-size: 0.9em;
            color: #666;
            text-transform: capitalize;
        }
        
        .user-username { 
            font-size: 0.9em;
            color: black;
            margin-top: -3px; 
            margin-bottom: 5px;
        }
        
        .change-photo-link {
            font-size: 0.8em;
            color: var(--mnp-blue);
            text-decoration: underline;
            cursor: pointer;
            margin-bottom: 10px;
        }
        
        .upload-form-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            display: none; 
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .upload-form-container.active {
            display: flex;
        }

        .upload-form-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 300px;
            text-align: center;
        }
        .upload-form-content h4 {
            margin-top: 0;
            color: var(--mnp-blue);
        }
        .upload-form-content input[type="file"] {
            margin: 15px 0;
        }
        .upload-form-content button {
            margin-top: 10px;
            padding: 10px 20px;
        }

        .logout-link {
            background-color: #dc3545; 
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
            align-self: center; 
        }
        .logout-link:hover {
            background-color: #c82333;
        }

        .container {
            padding: 0; 
            max-width: 100%; 
            margin: 0;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .module {
            background-color: var(--white);
            padding: 25px;
            border-radius: 8px;
            box-shadow: var(--shadow-light);
            border-top: 5px solid #28a745; 
            text-align: left;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .module:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        .module h3 {
            color: #333;
            margin-top: 0;
            font-size: 1.4em;
            margin-bottom: 10px;
        }
        .module p {
            font-size: 1em;
            color: #495057;
            margin-bottom: 15px;
            flex-grow: 1;
        }
        .module a {
            display: inline-block; 
            padding: 10px 15px;
            background-color: var(--mnp-blue);
            color: var(--white);
            text-decoration: none;
            font-weight: bold;
            border-radius: 4px;
            align-self: flex-start;
            transition: background-color 0.3s;
        }
        .module a:hover {
            background-color: var(--mnp-dark-blue);
        }

        .courses { border-top-color: #28a745; }
        .attachment { border-top-color: #ffc107; }
        .results { border-top-color: #17a2b8; }
        .support { border-top-color: #dc3545; }

        @media (max-width: 768px) {
            body {
                flex-direction: column; 
            }
            .sidebar {
                width: 100%; 
                height: auto;
                padding-top: 10px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            }
            .sidebar-header {
                padding: 10px 20px;
                margin-bottom: 10px;
            }
            .sidebar-menu a {
                border-radius: 0; 
            }
            .main-content {
                padding: 15px;
            }
            .top-bar {
                justify-content: center; 
                padding-bottom: 15px;
            }
            
            .dashboard-header {
                flex-direction: column;
            }
            .welcome-box {
                margin-right: 0;
                max-width: 100%;
                margin-bottom: 15px;
            }
            .profile-box {
                min-width: 100%;
                align-items: center;
            }
            .profile-details {
                margin-bottom: 15px;
            }
            .logout-link {
                align-self: center;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="images/logo.png" alt="MNP Logo" class="logo"> 
            <h2>MNP</h2>
        </div>
        
        <ul class="sidebar-menu">
            <li class="category-title">NAVIGATION</li>
            <li class="<?php echo ($current_view === 'dashboard' ? 'active' : ''); ?>">
                <a href="student_dashboard.php" class="with-dropdown">
                    <i class="fas fa-desktop"></i> My Dashboard
                    <i class="fas fa-chevron-down"></i>
                </a>
            </li>
            
            <li class="<?php echo ($current_view === 'mentoring' ? 'active' : ''); ?>">
                <a href="student_dashboard.php?view=mentoring">
                    <i class="fas fa-book"></i> Mentoring tool (New Entry)
                </a>
            </li>
            
            <li class="<?php echo ($current_view === 'saved_docs' ? 'active' : ''); ?>">
                <a href="student_dashboard.php?view=saved_docs">
                    <i class="fas fa-save"></i> Saved Documentation
                </a>
            </li>

            <li class="category-title">COURSE MANAGEMENT</li>
            <li>
                <a href="#">
                    <i class="fas fa-folder"></i> Courses
                </a>
            </li>

            <li class="category-title">INDUSTRIAL</li>
            <li>
                <a href="#">
                    <i class="fas fa-building"></i> Opportunities
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        
        <div id="uploadModal" class="upload-form-container">
            <div class="upload-form-content">
                <h4>Upload Profile Picture</h4>
                <form action="upload_profile_picture.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="profile_image" accept="image/*" required>
                    <button type="submit" class="btn btn-save">Upload Photo</button>
                    <button type="button" class="btn btn-back" onclick="closeModal()">Cancel</button>
                </form>
            </div>
        </div>
        
        <div class="dashboard-header">
            <div class="welcome-box">
                <h2>Welcome Back, <?php echo $first_name; ?></h2>
                <p>Welcome to your Collaborations & Linkages Portal. Use the links below to access your academic and industrial attachment resources.</p>
            </div>
            
            <div class="profile-box">
                <div class="profile-details">
                    <img src="<?php echo $profile_picture_path; ?>" alt="Profile Picture" class="profile-picture">
                    <div class="name-role">
                        <span class="user-name"><?php echo $first_name; ?></span>
                        <span class="user-role"><?php echo $role;?></span>
                    </div>
                </div>
                <a class="change-photo-link" onclick="openModal()">Change Photo</a>
                <a href="logout.php" class="logout-link">Log Out</a>
            </div>
        </div>
        <div class="container">
            
            <?php if ($current_view === 'mentoring'): ?>
                <?php include 'mentoring_tool.php'; ?>
            <?php elseif ($current_view === 'saved_docs'): ?>
                <?php include 'saved_documents_view.php'; ?>
            <?php else: ?>
                <div class="grid">
                    <div class="module attachment">
                        <h3>Attachment Information</h3>
                        <p>View your industrial attachment placement details, dates, and requirements.</p>
                        <a href="#">View Details</a>
                    </div>
                    <div class="module results">
                        <h3>View Results</h3>
                        <p>Check your latest course results and academic progress reports.</p>
                        <a href="#">View Results</a>
                    </div>
                    <div class="module support">
                        <h3>Help & Support</h3>
                        <p>Get in touch with the IT department for technical support or assistance.</p>
                        <a href="#">Contact Support</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('uploadModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('uploadModal').classList.remove('active');
        }
    </script>
</body>
</html>