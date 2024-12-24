<?php

    if (isset($_SESSION['user'])) {

        $stmt = $con->prepare("SELECT 
                                    id,
                                    Username, 
                                    Group_ID 
                                FROM 
                                    qma.users 
                                WHERE 
                                    (Username = ? AND Group_ID = 1) 
                                OR 
                                    (Username = ? AND Group_ID = 2)");

        $stmt->execute(array($_SESSION['user'], $_SESSION['user']));
        $row = $stmt->fetch();
        $adminCheck = $stmt->rowCount();

        if ($adminCheck > 0) {

            ?>
                <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                    <div class="container-fluid">
                        <a class="navbar-brand" href="index.php">إدارة قمة الهرم</a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="التبديل">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                <li class="nav-item">
                                    <a class="nav-link active" aria-current="page" href="index.php">الرئيسية</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="tasks.php">إدارة المعاملات</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="members.php">إدارة الأعضاء</a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <?php echo $_SESSION['user'] ?>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                        <li><a class="dropdown-item" href="profile.php">ملفي الشخصي</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="logout.php">تسجيل الخروج</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            <?php
    
        } else {

            ?>
                <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                    <div class="container-fluid">
                        <a class="navbar-brand" href="index.php">إدارة قمة الهرم</a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="التبديل">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                <li class="nav-item">
                                    <a class="nav-link active" aria-current="page" href="index.php">الرئيسية</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="tasks.php">المعاملات</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="members.php">الأعضاء</a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <?php echo $_SESSION['user'] ?>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                        <li><a class="dropdown-item" href="profile.php">ملفي الشخصي</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="logout.php">تسجيل الخروج</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            <?php
    
        }

    } else {

        ?>
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                <div class="container-fluid">
                    <a class="navbar-brand" href="index.php">إدارة قمة الهرم</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="التبديل">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link" href="login.php">تسجيل الدخول</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        <?php

    }
?>
