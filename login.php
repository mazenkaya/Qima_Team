<?php 
    session_start();

    $title = "تسجيل الدخول";

    include "connect.php";
    include "templates/functions.php";
    include "templates/header.php";

    if (isset($_SESSION['user'])) {

        header("Location: index.php");
        exit();

    } else {

        include "templates/nav.php";
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
            $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
            $hased_password = password_hash($password, PASSWORD_BCRYPT);

            $formErrors = [];
    
            if (empty($username)) {
                $formErrors[] = "لايمكن ترك حقل إسم المستخدم فارغا";
            }
    
            if (empty($password)) {
                $formErrors[] = "لايمكن ترك حقل كلمة المرور فارغا";
            }

            $stmt = $con->prepare("SELECT Password FROM qma.users WHERE Username = ?");
            $stmt->execute(array($username));
            $verifyPassword = $stmt->fetch();

            if (!password_verify($password, $verifyPassword['Password'])) {
                $formErrors[] = "كلمة المرور غير صحيحة";
            }
    
            if (!empty($formErrors)) {
    
                foreach ($formErrors as $error) {
    
                    echo "<div class='container mt-3'>";
                        echo "<div class='alert alert-danger'>" . $error . "</div>";
                    echo "</div>";
                    
                }
                
            } else {
    
                $stmt = $con->prepare("SELECT * FROM qma.users WHERE Username = ?");
                $stmt->execute(array($username));
                $count = $stmt->rowCount();
    
                if ($count > 0) {
                    
                    $_SESSION['user'] = $username;
    
                    header("Location: index.php");
                    exit();
    
                } else {
    
                    echo "<div class='container mt-3'>";
                        echo "<div class='alert alert-danger text-right'>إسم المستخدم أو كلمة المرور غير صحيحة</div>";
                    echo "</div>";
    
                }
            }
        }
        
        ?>
        <div class="container">
            <h2 class='text-center text-primary mt-5 mb-4'>تسجيل الدخول</h2>
            <form class="w-50 m-auto" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <div class="form-group position-relative">
                    <label for="username">إسم المستخدم</label>
                    <input type="text" class="form-control mb-3" name="username" id="username" data-text placeholder="أدخل إسم المستخدم" required>
                </div>
                <div class="form-group position-relative">
                    <label for="password">كلمة المرور</label>
                    <input type="password" class="form-control password mb-3" name="password" id="password" data-text placeholder="أدخل كلمة المرور" required>
                    <div class='show-pass'><i class="fa-regular fa-eye"></i></div>
                </div>
                <button type="submit" class="btn btn-primary">تسجيل الدخول</button>
            </form>
        </div>
        <?php
    }
        include "templates/footer.php";
    ?>
