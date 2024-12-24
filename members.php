<?php
        session_start();
        
        include "connect.php";

        $stmt = $con->prepare("SELECT Username FROM qma.users WHERE Username = ?");
        $stmt->execute(array($_SESSION['user']));
        $count = $stmt->rowCount();

    if ($count > 0) {

        $title = "الأعضاء";

        include "templates/functions.php";
        include "templates/header.php";
        include "templates/nav.php";

        $id = $_SESSION['id'];

        $do = isset($_GET['do']) ? $_GET['do'] : "manage";

        if ($do == "manage") {

            $stmt = $con->prepare("SELECT
                                        *
                                    FROM 
                                        qma.users 
                                    WHERE 
                                        users.Group_ID = 0 
                                    OR 
                                        users.Group_ID = 2");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $count = $stmt->fetchColumn();

            ?>
                <div class="container">
                    <?php

                        $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE id = ? AND Group_ID = 1 OR id = ? AND Group_ID = 2");
                        $stmt->execute(array($id, $id));
                        $adminCheck = $stmt->rowCount();

                        if ($adminCheck > 0) {
                            ?>
                                <h2 class="text-primary text-center mt-5 mb-4">إدارة الأعضاء</h2>

                            <?php
                        } else {
                            ?>
                                <h2 class="text-primary text-center mt-5 mb-4">الأعضاء</h2>
                            <?php
                        }
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="text-center">
                                <tr>
                                <th scope="col">الرقم</th>
                                <th scope="col">الإسم</th>
                                <th scope="col">البريد الإلكتروني</th>
                                <th scope="col">المنصب</th>
                                <th scope="col">المعاملات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                <?php
                                    foreach ($rows as $row) {
                                        echo "<tr>";
                                            echo "<td>" . $row['id'] . "</td>";
                                            echo "<td><a href='members.php?do=about&id=" . $row['id'] . "'</a>" . $row['Username'] . "</td>";
                                            echo  empty($row['Email']) ? "<td>لم يحدد</td>" : "<td>" . $row['Email'] . "</td>";
                                            echo "<td><span class='custom-span'>المنصب: </span>" . $row['Group_ID'] == 1 ? "<td>مدير</td>" : ($row['Group_ID'] == 2 ? "<td>محرر</td>" : "<td>عضو</td>") . "</td>";
                                            $stmt = $con->prepare("SELECT 
                                                                        COUNT(projects.id),
                                                                        projects.Member,
                                                                        users.id
                                                                    FROM
                                                                        qma.projects
                                                                    INNER JOIN
                                                                        qma.users
                                                                    ON
                                                                        users.id = projects.Member
                                                                    WHERE
                                                                        users.id = ?");                                        
                                            $stmt->execute(array($row['id']));
                                            $count = $stmt->fetchColumn();
                                            echo $count == 0 ? "<td>لايوجد بعد</td>" : "<td><a href='members.php?do=projects&id=" . $row['id'] . "'>" . $count . "</a></td>"; 
                                        echo "</tr>";
                                    }
                                ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    
                    <?php
                        if ($adminCheck > 0) {
                            ?>
                                <a href="members.php?do=add" class="btn btn-primary"><i class='fa fa-plus'></i> إضافة عضو</a>

                            <?php
                        }
                    ?>
                </div>
            <?php

        } elseif ($do == "about") {

            $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE id = ? AND Group_ID = 1 OR id = ? AND Group_ID = 2");
            $stmt->execute(array($_SESSION['id'], $_SESSION['id']));
            $adminCheck = $stmt->rowCount();

            $stmt = $con->prepare("SELECT users.id FROM qma.users WHERE id = ?");
            $stmt->execute(array($_GET['id']));
            $count = $stmt->rowCount();

            if ($count > 0) {

                $stmt = $con->prepare("SELECT * FROM qma.users WHERE id = ?");
                $stmt->execute(array($_GET['id']));
                $row = $stmt->fetch();

            ?>
                <div class="container">
                    <h2 class="text-center text-primary mt-5 mb-4">ملف  <?php echo $row['Username'] ?></h2>
                    <ul class="list-group p-0">
                        <li class="list-group-item list-group-item-action"><span class='custom-span'>الإسم: </span><?php echo $row['Username']; ?></li>
                        <li class="list-group-item list-group-item-action"><span class='custom-span'>الإسم الأول: </span><?php echo empty($row['First_Name']) ? "لم يحدد" : $row['First_Name']; ?></li>
                        <li class="list-group-item list-group-item-action"><span class='custom-span'>الإسم العائلة: </span><?php echo empty($row['Last_Name']) ? "لم يحدد" : $row['Last_Name']; ?></li>
                        <li class="list-group-item list-group-item-action"><span class='custom-span'>البريد الإلكتروني: </span><?php echo empty($row['Email']) ? "لم يحدد" : $row['Email']; ?></li>
                        <?php
                            $stmt = $con->prepare("SELECT 
                                                        COUNT(projects.id),
                                                        projects.Member,
                                                        users.id
                                                    FROM
                                                        qma.projects
                                                    INNER JOIN
                                                        qma.users
                                                    ON
                                                        users.id = projects.Member
                                                    WHERE
                                                        users.id = ?
                                                    ORDER BY 
                                                        projects.id DESC");                                        
                            $stmt->execute(array($row['id']));
                            $count = $stmt->fetchColumn();
                        ?>

                        <li class="list-group-item list-group-item-action"><span class='custom-span'>المعاملات: </span><?php echo $count == 0 ? "لايوجد بعد" : "<a href='members.php?do=projects&id=" . $row['id'] . "'>" . $count . "</a></li>"; ?>
                        <li class="list-group-item list-group-item-action"><span class='custom-span'>المنصب: </span><?php echo $row['Group_ID'] == 1 ? "مدير" : ($row['Group_ID'] == 2 ? "محرر" : "عضو");  ?></li>
                        <li class="list-group-item list-group-item-action"><span class='custom-span'>تاريخ التسجيل: </span><?php echo $row['Date']  ?></li>
                        <div class="box mt-4">
                            <a class='btn btn-info text-light' href='members.php?do=edit&id=<?php echo $row['id']?>'><i class='fa fa-edit'></i> تعديل بياناتي</a>
                        </div>
                    </ul>
                    <h5 class="text-primary mt-5 mb-4">آخر المعاملات</h5>
                    <ul class="list-group m-auto">
                        <?php
                            $stmt = $con->prepare("SELECT * FROM qma.projects WHERE Member = ? ORDER BY id DESC LIMIT 2");
                            $stmt->execute(array($row['id']));
                            $lastedProjects = $stmt->fetchAll();

                            if (!empty($lastedComment)) {

                                foreach ($lastedProjects as $project) {
                                    echo '<li class="list-group-item list-group-item-action">';
                                        echo '<span class="custom-span">';
                                            echo $project['Completed'] == 1 ? "<i class='fa-solid fa-check' style='color: #00ff2a;'></i> " : "";
                                            echo $project['Client_Name'];
                                        echo ':</span>';
                                        if ($project['Project_Task'] == 0) {
                                            echo "غير محدد";
                                        }
                                        if ($project['Project_Task'] == 1) {
                                            echo "مبدئي";
                                        } 
                                        if ($project['Project_Task'] == 2) {
                                            echo "مميز";
                                        }
                                        if ($project['Project_Task'] == 3) {
                                            echo "نهائي";
                                        }
                                        if ($project['Project_Task'] == 4) {
                                            echo "مبدئي ونهائي";
                                        }
                                        echo "<span class='pull-left'>";
                                            echo " <a class='btn btn-success mb-1' href='tasks.php?do=detials&id=" . $project['id'] . "'><i class='fa fa-info'></i></a> ";
    
                                            if ($project["Member"] == $row['id'] || $adminCheck > 0) {
    
                                                echo "<a class='btn btn-info mb-1 text-light' href='tasks.php?do=edit&id=" . $project['id'] . "'><i class='fa fa-edit'></i></a> ";
    
                                            } 
    
                                            if ($adminCheck > 0) {
                            
                                                echo "<a class='btn btn-danger confirm mb-1' href='tasks.php?do=delete&id=" . $project['id'] . "'><i class='fa fa-close'></i></a>";
    
                                            }
                                        echo "</span>";
                                        echo "<br><br><span class='pull-left bg-secondary text-light ps-1 pe-1 rounded'><small>" . $project['Date'] . "</small></span>";
                                    echo '</li>';
                                }

                            } else {

                                echo "<p>لايوجد معاملة بعد</p>";

                            }


                        ?>
                    </ul>
                    <h5 class="text-primary mt-5 mb-4">آخر التعليقات</h5>
                    <ul class="list-group m-auto">
                        <?php
                            $stmt = $con->prepare("SELECT
                                                        comment.*,
                                                        projects.Client_Name 
                                                    FROM 
                                                        qma.comment
                                                    INNER JOIN
                                                        qma.projects
                                                    ON
                                                        projects.id = comment.Project_ID
                                                    WHERE 
                                                        User_ID = ? 
                                                    ORDER BY 
                                                        id DESC LIMIT 2");
                            $stmt->execute(array($row['id']));
                            $lastedComment = $stmt->fetchAll();

                            if (!empty($lastedComment)) {

                                foreach ($lastedComment as $comment) {
                                    echo '<li class="list-group-item list-group-item-action">';
                                        echo '<span class="fw-bold"> التعليق: ';
                                        echo '</span> "';
                                        echo $comment['Comment'] . "\"";
                                        echo "<span class='pull-left'>";
                                        echo " <a class='btn btn-success mb-1' href='tasks.php?do=detials&id=" . $comment['Project_ID'] . "'><i class='fa fa-info'></i></a> ";

                                        if ($comment["User_ID"] == $row['id']) {

                                            echo "<a class='btn btn-info mb-1 text-light' href='tasks.php?do=editcomment&id=" . $comment['id'] . "'><i class='fa fa-edit'></i></a> ";

                                        } 

                                        if ($adminCheck > 0) {
                        
                                            echo "<a class='btn btn-danger confirm mb-1' href='tasks.php?do=deletecomment&id=" . $comment['id'] . "'><i class='fa fa-close'></i></a>";

                                        }
                                    echo "</span>";
                                        echo " <hr><b class=''> في معاملة: </b>" . $comment['Client_Name'];
                                        echo "<span class='pull-left bg-secondary text-light ps-1 pe-1 rounded'><small>" . $comment['Date'] . "</small></span>";
                                    echo '</li>';
                                }
                            } else {
                                
                                echo "<p>لايوجد تعليقات بعد</p>";
                                
                            }
                        ?>
                    </ul>

                </div>           
            <?php

            } else {
    
                echo "<div class='container'>";
                    echo "<div class='alert alert-warning mt-5'>هذا العضو غير موجود</div>";
                    echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة إدارة الأعضاء بعد 5 ثوان</div>";
                    header("refresh:5;url=members.php");
                    exit();
                echo "</div>";

            }
            
        } elseif ($do == "add") {

            $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE id = ? AND Group_ID = 1 OR id = ? AND Group_ID = 2");
            $stmt->execute(array($_SESSION['id'], $_SESSION['id']));
            $adminCheck = $stmt->rowCount();

            if ($adminCheck > 0) {
                ?>
                    <div class="container">
                        <h2 class="text-primary text-center mt-5 mb-4">إضافة عضو جديد</h2>
                        <form class='w-50 m-auto' action="members.php?do=insert" method="POST">

                            <div class="form-group mb-3 position-relative">
                                <label for="username">إسم العضو</label>
                                <input type="text" class="form-control" name="username" id="username" placeholder="أدخل إسم العميل. يجب أن يكون مميزا" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="first-name">الإسم الأول</label>
                                <input type="text" class="form-control" name="first-name" id="first-name" placeholder="أدخل الإسم الأول">
                            </div>

                            <div class="form-group mb-3">
                                <label for="last-name">إسم العائلة</label>
                                <input type="text" class="form-control" name="last-name" id="last-name" placeholder="أدخل إسم العائلة">
                            </div>

                            <div class="form-group mb-3">
                                <label for="email">البريد الإلكتروني</label>
                                <input type="text" class="form-control" name="email" id="email" placeholder="أدخل البريد الإلكتروني">
                            </div>

                            <div class="form-group mb-3 position-relative">
                                <label for="password">كلمة المرور</label>
                                <input type="password" class="form-control password" name="password" id="password" placeholder="أدخل كلمة المرور" required>
                                <div class='show-pass'><i class="fa-regular fa-eye"></i></div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="projects-number">عدد المعاملات</label>
                                <input type="number" class="form-control" name="projects-number" id="projects-number" placeholder="أدخل عدد المعاملات">
                            </div>

                            <label class="mb-2">الرتبة</label>
                            <select class="form-select mb-4" name="group-id" id="group-id">
                                <option value="0">عضو</option>
                                <option value="2">محرر</option>
                                <option value="1">مدير</option>
                            </select>
                            <button type="submit" class="btn btn-primary">إضافة</button>
                        </form>

                    </div>
                
                <?php

            } else {
                echo "<div class='container'>";
                    echo "<div class='alert alert-warning mt-5'>غير مصرح لك بالدخول إلى هذا الرابط</div>";
                    echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة الأعضاء بعد 5 ثوان</div>";
                    header("refresh:5;url=members.php");
                    exit();
                echo "</div>";
                
            }



        } elseif ($do == "insert") {
            
            if ($_SERVER['REQUEST_METHOD'] == "POST") {

                $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
                $firstName = filter_var($_POST['first-name'], FILTER_SANITIZE_STRING);
                $lastName = filter_var($_POST['last-name'], FILTER_SANITIZE_STRING);
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
                $hased_password = password_hash($password, PASSWORD_BCRYPT);
                $projectNumbers = filter_var($_POST['projects-number'], FILTER_SANITIZE_NUMBER_INT);
                $groupId = filter_var($_POST['group-id'], FILTER_SANITIZE_NUMBER_INT);

                $formErrors = [];

                if (empty($username)) {
                    $formErrors[] = "لايمكن ترك حقل اسم العضو فارغا";
                }
                if (empty($password)) {
                    $formErrors[] = "لايمكن ترك حقل كلمة المرور فارغا";
                }
                if ($groupId > 2 || $groupId < 0) {
                    $formErrors[] = "لايمكن ترك حقل رتبة العضو فارغا";
                }

                if (!empty($formErrors)) {
                        echo "<div class='container mt-5'>";
                            foreach ($formErrors as $error) {
                                    echo "<div class='alert alert-danger'>" . $error . "</div>";
                            }
                            echo "<div class='alert alert-info'>سيتم تحديث الصفحة بعد 5 ثواني</div>";
                            header("refresh:5; url=members.php?do=add");
                            exit();
                        echo "</div>";
                } else {

                    $stmt = $con->prepare("INSERT INTO 
                    qma.users (
                                        Username,
                                        First_Name, 
                                        Last_Name, 
                                        Email, 
                                        Password, 
                                        Projects, 
                                        Date, 
                                        Group_ID) 
                                VALUES (?, ?, ?, ?, ?, ?, now(), ?)");
                    $stmt->execute(array($username, $firstName, $lastName, $email, $hased_password, $projectNumbers, $groupId));
                    $count = $stmt->rowCount();
        
                    if ($count > 0) {
        
                    echo "<div class='container'>";
                        echo "<div class='alert alert-success mt-5'>تم إضافة عضو جديد بنجاح</div>";
                        echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة إدارة الأعضاء بعد 5 ثواني</div>";
                        header("refresh:5; url=members.php");
                        exit();
                    echo "</div>";


        
                    } else {
        
                    echo "<div class='container'>";
                        echo "<div class='alert alert-danger mt-5'>حدث خطأ حاول مرة أخرى</div>";
                        echo "<div class='alert alert-info'>سيتم تحديث الصفحة بعد 5 ثواني</div>";
                        header("refresh:5; url=members.php?do=add");
                        exit();
                    echo "</div>";

                    }

                }

            }
            
        } elseif ($do == "edit") {

            $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE (id = ? AND Group_ID = 1)");
            $stmt->execute(array($_SESSION['id']));
            $isAdmin = $stmt->rowCount();

            if ($isAdmin > 0 || $_GET['id'] == $_SESSION['id']) {

                $stmt = $con->prepare("SELECT * FROM qma.users WHERE id = ?");
                $stmt->execute(array($_GET['id']));
                $count = $stmt->rowCount();
                $row = $stmt-> fetch();
    
                if ($count > 0) {
    
                    ?>
                        <div class="container">
                            <?php
                                if ($_GET['id'] == $_SESSION['id']) {
                                    ?>
                                        <h2 class="text-primary text-center mt-5 mb-4">تعديل بياناتي</h2>

                                    <?php
                                } else {
                                    ?>
                                    
                                        <h2 class="text-primary text-center mt-5 mb-4">تعديل بيانات العضو</h2>
                                    <?php
                                }
                            ?>
                            <form class='w-50 m-auto' action="members.php?do=update" method="POST">
    
                                <input type="hidden" name="id" value="<?php echo $_GET['id'] ?>">

                                <?php

                                    if ($isAdmin > 0) {

                                        ?>

                                            <div class="form-group mb-3 position-relative">
                                                <label for="username">إسم العضو</label> 
                                                <input type="text" class="form-control" name="username" id="username" value="<?php echo $row['Username']; ?>" placeholder="أدخل إسم العميل. يجب أن يكون مميزا" required>
                                            </div>

                                        <?php

                                    } else {

                                        ?>
                                            <div class="form-group mb-3 position-relative">
                                                <label for="username">إسم العضو</label> 
                                                <input type="text" class="form-control" name="username" id="username" value="<?php echo $row['Username']; ?>" readonly>
                                            </div>
                                        <?php

                                    }
                                ?>
    
                                <div class="form-group mb-3">
                                    <label for="first-name">الإسم الأول</label>
                                    <input type="text" class="form-control" name="first-name" id="first-name" value="<?php echo $row['First_Name']; ?>" placeholder="أدخل الإسم الأول">
                                </div>
    
                                <div class="form-group mb-3">
                                    <label for="last-name">إسم العائلة</label>
                                    <input type="text" class="form-control" name="last-name" id="last-name" value="<?php echo $row['Last_Name']; ?>" placeholder="أدخل إسم العائلة">
                                </div>

                                <?php

                                if ($isAdmin > 0) {

                                    ?>

                                        <div class="form-group mb-3">
                                            <label for="email">البريد الإلكتروني</label>
                                            <input type="text" class="form-control" name="email" id="email" value="<?php echo $row['Email']; ?>" placeholder="أدخل البريد الإلكتروني">
                                        </div>
                                        <div class="form-group mb-3 position-relative">
                                            <label for="password">كلمة المرور</label>
                                            <input type="password" class="form-control password" name="password" id="password" placeholder="أدخل كلمة المرور" required>
                                            <div class='show-pass'><i class="fa-regular fa-eye"></i></div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="projects-number">عدد المعاملات</label>
                                            <input type="number" class="form-control" name="projects-number" id="projects-number" value="<?php echo $row['Projects'] ?>" placeholder="أدخل عدد المعاملات">
                                        </div>
                                        <label class="mb-2">المنصب</label>
                                        <select class="form-select mb-4" name="group-id" id="group-id">
                                            <option <?php echo $row['Group_ID'] == 0 ? "selected" : ""?> value="0">عضو</option>
                                            <option <?php echo $row['Group_ID'] == 1 ? "selected" : ""?> value="1">مدير</option>
                                            <option <?php echo $row['Group_ID'] == 2 ? "selected" : ""?> value="2">محرر</option>
                                        </select>

                                    <?php

                                } else {
                                    ?>
                                        <div class="form-group mb-3">
                                            <label for="email">البريد الإلكتروني</label>
                                            <input type="text" class="form-control" name="email" id="email" value="<?php echo $row['Email']; ?>" readonly>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="projects-number">عدد المعاملات</label>
                                            <input type="number" class="form-control" name="projects-number" id="projects-number" value="<?php echo $row['Projects'] ?>" readonly>
                                        </div>
                                        <label class="mb-2">المنصب</label>
                                        <select class="form-select mb-4" name="group-id" id="group-id">
                                            <option value="<?php echo $row['Group_ID']; ?>" selected><?php echo $row['Group_ID'] == 0 ? "عضو" : ($row['Group_ID'] == 2 ? "محرر" : "مدير"); ?></option>
                                        </select>

                                    <?php
                                }
                                
                                ?>

                                <button type="submit" class="btn btn-primary">حفظ</button>
                            </form>
    
                        </div>
    
                    <?php
    
                } else {
    
                    echo "<div class='container'>";
                        echo "<div class='alert alert-warning mt-5'>هذا العضو غير موجود</div>";
                        echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة إدارة الأعضاء بعد 5 ثوان</div>";
                        header("refresh:5;url=members.php");
                        exit();
                    echo "</div>";
                    
                }

            } else {

                echo "<div class='container'>";
                    echo "<div class='alert alert-warning mt-5'>غير مصرح لك بالدخول إلى هذا الرابط</div>";
                    echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة الأعضاء بعد 5 ثوان</div>";
                    header("refresh:5;url=members.php");
                    exit();
                echo "</div>";
            }

        } elseif ($do == "update") {

            if ($_SERVER['REQUEST_METHOD'] == "POST") {

                $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
                $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
                $firstName = filter_var($_POST['first-name'], FILTER_SANITIZE_STRING);
                $lastName = filter_var($_POST['last-name'], FILTER_SANITIZE_STRING);
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
                $hased_password = password_hash($password, PASSWORD_BCRYPT);
                $projectNumbers = filter_var($_POST['projects-number'], FILTER_SANITIZE_NUMBER_INT);
                $groupId = filter_var($_POST['group-id'], FILTER_SANITIZE_NUMBER_INT);

                $formErrors = [];

                if (empty($username)) {
                    $formErrors[] = "لايمكن ترك حقل اسم العضو فارغا";
                }
                if (empty($password)) {
                    $formErrors[] = "لايمكن ترك حقل كلمة المرور فارغا";
                }
                if ($groupId > 2 || $groupId < 0) {
                    $formErrors[] = "لايمكن ترك حقل منصب العضو فارغا";
                }

                if (!empty($formErrors)) {
                        echo "<div class='container mt-5'>";
                            foreach ($formErrors as $error) {
                                    echo "<div class='alert alert-danger'>" . $error . "</div>";
                            }
                            echo "<div class='alert alert-info'>سيتم تحديث الصفحة بعد 5 ثواني</div>";
                            header("refresh:5; url=members.php?do=add");
                            exit();
                        echo "</div>";
                } else {

                    $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE (id = ? AND Group_ID = 1)");
                    $stmt->execute(array($_SESSION['id']));
                    $isAdmin = $stmt->rowCount();

                    if ($isAdmin > 0) {

                        $stmt = $con->prepare("UPDATE qma.users SET 
                                                        Username = ?,
                                                        First_Name = ?, 
                                                        Last_Name = ?, 
                                                        Email = ?, 
                                                        Password = ?, 
                                                        Projects = ?, 
                                                        Group_ID = ?

                                                            WHERE id = ?");
                        $stmt->execute(array($username, $firstName, $lastName, $email, $hased_password, $projectNumbers, $groupId, $id));
                        $count = $stmt->rowCount();

                        if ($count > 0) {

                            echo "<div class='container'>";
                                echo "<div class='alert alert-success mt-5'>تم تعديل بيانات العضو بنجاح</div>";
                                echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة العضو المحدثة بعد 5 ثواني</div>";
                            header("refresh:5; url=members.php?do=about&id=" . $id);
                            exit();
                            echo "</div>";

                        } else {

                            echo "<div class='container'>";
                                echo "<div class='alert alert-danger mt-5'>حدث خطأ حاول مرة أخرى</div>";
                                echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة العضو المحدثة بعد 5 ثواني</div>";
                            header("refresh:5; url=members.php?do=about&id=" . $id);
                            exit();
                            echo "</div>";

                        }

                    } else {
                        
                        $stmt = $con->prepare("SELECT * FROM qma.users WHERE id = ?");
                        $stmt->execute(array($_SESSION['id']));
                        $row = $stmt->fetch();

                        if ($username == $row['Username'] && $email == $row['Email'] && $hased_password == $row['Password'] && $groupId == $row['Group_ID'] && $projectNumbers == $row['Projects']) {

                            $stmt = $con->prepare("UPDATE qma.users SET 
                                                            Username = ?,
                                                            First_Name = ?, 
                                                            Last_Name = ?, 
                                                            Email = ?, 
                                                            Password = ?, 
                                                            Projects = ?, 
                                                            Group_ID = ?
                                                    WHERE 
                                                            id = ?");

                            $stmt->execute(array($username, $firstName, $lastName, $email, $hased_password, $projectNumbers, $groupId, $_SESSION['id']));
                            $count = $stmt->rowCount();

                            if ($count > 0) {

                                echo "<div class='container'>";
                                    echo "<div class='alert alert-success mt-5'>تم تعديل بيانات العضو بنجاح</div>";
                                    echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة العضو المحدثة بعد 5 ثواني</div>";
                                header("refresh:5; url=members.php?do=about&id=" . $id);
                                exit();
                                echo "</div>";

                            } else {

                                echo "<div class='container'>";
                                    echo "<div class='alert alert-danger mt-5'>حدث خطأ أو لم يتم تحديث البيانات</div>";
                                    echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة العضو المحدثة بعد 5 ثواني</div>";
                                header("refresh:5; url=members.php?do=about&id=" . $id);
                                exit();
                                echo "</div>";

                            }    

                        } else {

                            echo "<div class='container'>";
                                echo "<div class='alert alert-danger mt-5'>غير مصرح لك بتغيير البيانات بهذه الطريقة</div>";
                                echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة بيانات العضو بعد 5 ثواني</div>";
                            header("refresh:5; url=members.php?do=detials&id=" . $_SESSION['id']);
                            exit();
                            echo "</div>"; 
                        }
                    
                    }
                }
            }

        } elseif ($do == "delete") {

            $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE (id = ? AND Group_ID = 1)");
            $stmt->execute(array($_SESSION['id']));
            $isAdmin = $stmt->rowCount();

            if ($isAdmin > 0 && $_GET['id'] != $_SESSION['id']) {

                $stmt = $con->prepare("SELECT * FROM qma.users WHERE id = ?");
                $stmt->execute(array($_GET['id']));
                $count = $stmt->rowCount();
    
                if ($count > 0) {
    
                    $stmt = $con->prepare("DELETE FROM qma.users WHERE id = ?");
                    $stmt->execute(array($_GET['id']));
                    $count = $stmt->rowCount();
    
                    echo "<div class='container mt-5 mb-3'>";
                        echo "<div class='alert alert-success'>تم حذف العضو بنجاح</div>";
                        echo "<div class='alert alert-info'>سيتم تحديث الصفحة بعد 5 ثواني</div>";
                        header("refresh:5; url=members.php");
                        exit();
                    echo "</div>";
                    
                } else {
    
                    echo "<div class='container'>";
                        echo "<div class='alert alert-warning mt-5'>هذا العضو غير موجود</div>";
                        echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة إدارة الأعضاء بعد 5 ثوان</div>";
                        header("refresh:5;url=members.php");
                        exit();
                    echo "</div>";
    
                }

            } else {
                
                echo "<div class='container'>";
                    echo "<div class='alert alert-warning mt-5'>غير مصرح لك بالدخول إلى هذا الرابط</div>";
                    echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة الأعضاء بعد 5 ثوان</div>";
                    header("refresh:5;url=members.php");
                    exit();
                echo "</div>";

            }


        } elseif ($do == "projects") {

            $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE id = ? AND Group_ID = 1 OR id = ? AND Group_ID = 2");
            $stmt->execute(array($_SESSION['id'], $_SESSION['id']));
            $adminCheck = $stmt->rowCount();

            $stmt = $con->prepare("SELECT * FROM qma.projects WHERE Member = ?");
            $stmt->execute(array($_GET['id']));
            $count = $stmt->rowCount();

            if ($count > 0) {

                $stmt = $con->prepare("SELECT 
                                            projects.*,
                                            users.id AS user_id,
                                            users.Username 
                                        FROM
                                            qma.projects
                                        INNER JOIN
                                            qma.users
                                        ON
                                            users.id = projects.Member
                                        WHERE 
                                            users.id = ?");

                $stmt->execute(array($_GET['id']));
                $rows = $stmt->fetchAll();

                ?>
                    <div class="container">
                        <h2 class="text-primary text-center mt-5 mb-4">إدارة المعاملات</h2>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-responsive">
                                <thead class="text-center">
                                    <tr>
                                    <th scope="col">رقم المعاملة</th>
                                    <th scope="col">إسم الشركة أو العميل</th>
                                    <th scope="col">تاريخ إستلام المعاملة</th>
                                    <th scope="col">نوع المعاملة</th>
                                    <th scope="col">عدد الوحدات</th>
                                    <th scope="col">رابط المعاملة</th>
                                    <th scope="col">إجراءات وملاحظات</th>
                                    <th scope="col">تنفيذ الأعمال</th>
                                    <th scope="col">التحكم</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                    <?php
                                        foreach ($rows as $row) {
                                            echo "<tr>";
                                                echo "<td>";
                                                    echo $row['id'];
                                                    echo $row['Completed'] == 1 ? " <i class='fa-solid fa-check' style='color: #00ff2a;'></i>" : "" ;
                                                echo "</td>";
                                                echo "<td>" . $row['Client_Name'] . "</td>";
                                                echo "<td>" . $row['Date'] . "</td>";
                                                echo "<td>";
                                                    if ($row['Project_Task'] == 0) {
                                                        echo "غير محدد";
                                                    }
                                                    if ($row['Project_Task'] == 1) {
                                                        echo "مبدئي";
                                                    } 
                                                    if ($row['Project_Task'] == 2) {
                                                        echo "مميز";
                                                    }
                                                    if ($row['Project_Task'] == 3) {
                                                        echo "نهائي";
                                                    }
                                                    if ($row['Project_Task'] == 4) {
                                                        echo "مبدئي ونهائي";
                                                }
                                                echo "</td>";
                                                echo "<td>";
                                                    if ($row['Units_Number'] == 0) {
                                                        echo "غير محدد";
                                    
                                                    } else {
                                                        echo $row['Units_Number'] . "</td>";
                                                    }
                                                echo "<td>";
                                                    if (empty($row['Project_Link'])) {
                                                        echo "غير محدد";
                                    
                                                    } else {
                                                        echo $row['Project_Link'] . "</td>";
                                                    }
                                                echo "</td>";
                                                echo "<td>";
                                                    if (empty($row['Comments'])) {
                                                        echo "غير محدد";
                                    
                                                    } else {
                                                        echo $row['Comments'] . "</td>";
                                                    }
                                                echo "</td>";
                                                echo "<td><a href='members.php?do=about&id=" . $row['user_id'] . "'>" . $row['Username'] . "</a></td>";
                                                echo "<td>";
                                                    echo "<a class='btn btn-success mb-1' href='tasks.php?do=detials&id=" . $row['id'] . "'><i class='fa fa-info'></i></a> ";

                                                    if ($adminCheck > 0 || $_GET['id'] == $_SESSION['id']) {

                                                        echo "<a class='btn btn-info text-light mb-1' href='tasks.php?do=edit&id=" . $row['id'] . "'><i class='fa fa-edit'></i></a> ";

                                                    }

                                                    if ($adminCheck > 0) {

                                                        echo "<a class='btn btn-danger confirm mb-1' href='tasks.php?do=delete&id=" . $row['id'] . "'><i class='fa fa-close'></i></a>";

                                                    }

                                                echo "</td>";
                                            echo "</tr>";
                                        }
                                    ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <?php
                            if ($adminCheck > 0) {
                                ?>
                                    <a href="tasks.php?do=add" class="btn btn-primary">إضافة معاملة</a>

                                <?php
                            }
                        ?>
                    </div>
                <?php
                
            } else {

                echo "<div class='container'>";
                    echo "<div class='alert alert-warning mt-5'>هذا العضو غير موجود أو ليس لديه معاملات</div>";
                    echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة إدارة الأعضاء بعد 5 ثوان</div>";
                    header("refresh:5;url=members.php");
                    exit();
                echo "</div>";

            }

        }
        
    } else {

        header("Location: login.php");
        exit();

    }
        include "templates/footer.php";
    ?>