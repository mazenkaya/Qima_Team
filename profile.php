<?php
    session_start();

    include "connect.php";

    $stmt = $con->prepare("SELECT Username FROM qma.users WHERE Username = ?");
    $stmt->execute(array($_SESSION['user']));
    $count = $stmt->rowCount();
    
    if ($count > 0) {

        $title = "الملف الشخصي";
        
        include "templates/functions.php";
        include "templates/header.php";
        include "templates/nav.php";

        $stmt = $con->prepare("SELECT 
                                    Username, 
                                    Group_ID 
                                FROM 
                                    qma.users 
                                WHERE 
                                    (Username = ? AND Group_ID = 1) 
                                OR 
                                    (Username = ? AND Group_ID = 2)");

        $stmt->execute(array($_SESSION['user'], $_SESSION['user']));
        $adminCheck = $stmt->rowCount();

        $stmt = $con->prepare("SELECT * FROM qma.users WHERE id = ?");
        $stmt->execute(array($_SESSION['id']));
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
                <ul class="list-group p-0">
                    <?php
                        $stmt = $con->prepare("SELECT * FROM qma.projects WHERE Member = ? ORDER BY id DESC LIMIT 2");
                        $stmt->execute(array($row['id']));
                        $lastedProjects = $stmt->fetchAll();

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
                    ?>
                </ul>
                <h5 class="text-primary mt-5 mb-4">آخر التعليقات</h5>
                <ul class="list-group p-0">
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
                    ?>
                </ul>

            </div>           
        <?php

    } else {

        header("Location: login.php");
        exit();

    }

    include "templates/footer.php";

?>