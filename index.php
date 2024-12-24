<?php
    session_start();

    include 'connect.php';

    $stmt = $con->prepare("SELECT Username FROM qma.users WHERE Username = ?");
    $stmt->execute(array($_SESSION['user']));
    $count = $stmt->rowCount();

    if ($count > 0) {

        $title = "الرئيسية";

        include 'templates/functions.php';
        include 'templates/header.php';
        include 'templates/nav.php';

        $stmt = $con->prepare("SELECT id FROM qma.users WHERE Username = ?");
        $stmt->execute(array($_SESSION['user']));
        $id = $stmt->fetch();

        $_SESSION['id'] = $id['id'];

        ?>

            <div class="container home-stats">
                <h1 class='text-center text-primary mt-5 mb-5'>الرئيسية</h1>
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="stat st-members mb-2">
                            <i class="fa fa-tag"></i>
                            <div class="info">
                                المعاملات
                                <span>
                                    <?php
                                        $stmt = $con->prepare("SELECT COUNT(id) FROM qma.projects");
                                        $stmt->execute();
                                        $count = $stmt->fetchColumn();
                                    ?>
                                        <a href="tasks.php?do=manage"><?php echo $count ?></a>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat st-pending mb-2">
                            <i class="fa fa-users"></i>
                            <div class="info">
                            <?php
                                $stmt = $con->prepare("SELECT COUNT(id) FROM qma.users WHERE Group_ID != 1");
                                $stmt->execute();
                                $count = $stmt->fetchColumn();
                            ?>
                                الأعضاء <span><a href="members.php?do=manage"><?php echo $count ?></a></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat st-items mb-2">
                            <i class="fa fa-tag"></i>
                            <div class="info">
                                قريبا <span><a href="#">...</a></span>
                            </div> 
                        </div>
                    </div>
                    <div class="col-md-3">
                                <div class="stat st-comments mb-2">
                                    <i class="fa fa-comments"></i>
                                    <div class="info">
                                    <?php
                                        $stmt = $con->prepare("SELECT COUNT(id) FROM qma.comment");
                                        $stmt->execute();
                                        $count = $stmt->fetchColumn();
                                    ?>
                                        التعليقات
                                        <span><?php echo $count ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <div class="latest">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="panel panel-default">
                                    <?php
                                        $stmt = $con->prepare("SELECT
                                                                    *
                                                                FROM 
                                                                    qma.projects
                                                                ORDER BY 
                                                                    id DESC");
                                        $stmt->execute();
                                        $rows = $stmt->fetchAll();
                                        $count = $stmt->rowCount();
                                        ?>
                                    <div class="panel-heading">
                                        <i class="fa fa-tag"></i> <h6 class='d-inline'>آخر 5 معاملات</h6>
                                        <span class='toggle-info pull-left'>
                                            <i class='fa fa-plus fa-lg'></i>
                                        </span>
                                    </div>
                                    <div class="panel-body">
                                        <ul class="list-unstyled latest-users mt-2">
                                            <?php

                                                if ($count > 0) {

                                                    $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE (Username = ? AND Group_ID = 1) OR (Username = ? AND Group_ID = 2)");
                                                    $stmt->execute(array($_SESSION['user'], $_SESSION['user']));
                                                    $adminCheck = $stmt->rowCount();
            
                                                    foreach($rows as $row) {
            
                                                        echo "<li>";
                                                            echo  $row['Client_Name'];
                                                            echo "<span class='pull-left'>";
            
                                                                echo "<a class='btn btn-success' href='tasks.php?do=detials&id=" . $row['id'] . "'><i class='fa fa-info'></i></a>";
            
            
                                                                if ($row["Member"] == $_SESSION['id'] || $adminCheck > 0) {
            
                                                                    echo "<a class='btn btn-info text-light' href='tasks.php?do=edit&id=" . $row['id'] . "'><i class='fa fa-edit'></i></a>";
            
                                                                } 
                                                                
                                                                if ($adminCheck > 0) {
            
                                                                    echo "<a class='btn btn-danger confirm' href='tasks.php?do=delete&id=" . $row['id'] . "'><i class='fa fa-close'></i></a>";
                                                                    
                                                                }
            
                                                            echo "</span>";
                                                        echo "</li>";
            
                                                    }

                                                } else {

                                                    echo "<p>لاتوجد معاملات حاليا</p>";

                                                }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="panel panel-default">
                                    <?php
                                        $stmt = $con->prepare("SELECT 
                                                                    comment.*,
                                                                    users.Username
                                                                FROM 
                                                                    qma.comment 
                                                                INNER JOIN
                                                                    qma.users
                                                                ON
                                                                    users.id = comment.User_ID
                                                                ORDER BY 
                                                                    comment.id DESC");
                                        $stmt->execute();
                                        $rows = $stmt->fetchAll();
                                        $count = $stmt->rowCount();

                                        $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE (id = ? AND Group_ID = 1) OR (id = ? AND Group_ID = 2)");
                                        $stmt->execute(array($_SESSION['id'], $_SESSION['id']));
                                        $adminCheck = $stmt->rowCount();

                                        ?>
                                    <div class="panel-heading">
                                        <i class="fa fa-comments"></i> <h6 class='d-inline'>آخر 5 تعليقات</h6>
                                        <span class='toggle-info pull-left'>
                                            <i class='fa fa-plus fa-lg'></i>
                                        </span>
                                    </div>
                                    <div class="panel-body">
                                        <ul class="list-unstyled latest-users mt-2">
                                            <?php

                                                if ($count > 0) {

                                                    foreach($rows as $row) {
                                                    
                                                        echo "<li>";
                                                            echo "<span class='comment-user'>" . $row['Username'] . ": </span>";
                                                            echo  $row['Comment'];
                                                            echo "<span class='pull-left'>";
            
                                                                echo "<a class='btn btn-success' href='tasks.php?do=detials&id=" . $row['Project_ID'] . "'><i class='fa fa-info'></i></a>";
            
            
                                                                if ($row['User_ID'] == $_SESSION['id']) {
            
                                                                    echo "<a class='btn btn-info text-light' href='tasks.php?do=detials&id=" . $row['Project_ID'] . "'><i class='fa fa-edit'></i></a>";
            
                                                                }
            
                                                                if ($adminCheck > 0) {
            
                                                                    echo "<a class='btn btn-danger confirm' href='tasks.php?do=delete&id=" . $row['Project_ID'] . "'><i class='fa fa-close'></i></a>";
            
                                                                }
            
                                                            echo "</span>";
                                                        echo "</li>";
            
                                                    }

                                                } else {
                                                    
                                                    echo "<p>لاتوجد تعليقات حاليا</p>";

                                                }



                                            
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php

    } else {

        header('Location: login.php');
        exit();

    }

    include 'templates/footer.php';