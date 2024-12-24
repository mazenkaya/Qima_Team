<?php
        session_start();

        include "connect.php";
        
        $stmt = $con->prepare("SELECT Username FROM qma.users WHERE Username = ?");
        $stmt->execute(array($_SESSION['user']));
        $count = $stmt->rowCount();

    if ($count > 0) {

        $title = "المعاملات";

        include "templates/functions.php";
        include "templates/header.php";
        include "templates/nav.php";

        $id = $_SESSION['id'];

        $do = isset($_GET['do']) ? $_GET['do'] : "manage";

        if ($do == "manage") {

            $stmt = $con->prepare("SELECT
                                        projects.*,
                                        users.Username  
                                    FROM 
                                        qma.projects
                                    INNER JOIN
                                        qma.users
                                    ON
                                        users.id = projects.Member");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $count = $stmt->rowCount();

            ?>
                <div class="container">
                    
                    <?php
                        $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE (id = ? AND Group_ID = 1) OR (id = ? AND Group_ID = 2)");
                        $stmt->execute(array($id, $id));
                        $adminCheck = $stmt->rowCount();
            
                        if ($adminCheck > 0) {
            
                            ?>
                                <h2 class="text-primary text-center mt-5 mb-4">إدارة المعاملات</h2>
                            <?php
            
                        } else {
            
                            ?>
                                <h2 class="text-primary text-center mt-5 mb-4">المعاملات</h2>
                            <?php
            
                        }
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="text-center">
                                <tr>
                                <th scope="col">رقم المعاملة</th>
                                <th scope="col">إسم الشركة أو العميل</th>
                                <th scope="col">تاريخ إستلام المعاملة</th>
                                <th scope="col">نوع المعاملة</th>
                                <th scope="col">عدد الوحدات</th>
                                <th scope="col">رابط المعاملة</th>
                                <th scope="col">آخر التعليقات</th>
                                <th scope="col">تنفيذ الأعمال</th>
                                <th class='text-center' scope="col">التحكم</th>
                                </tr>
                            </thead>
                            <tbody>
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
                                                    echo "<a href='". $row['Project_Link'] ."'>الرابط</a></td>";
                                                }
                                            echo "</td>";

                                            $stmt = $con->prepare("SELECT 
                                                                        comment.*,
                                                                        users.Username
                                                                    FROM 
                                                                        qma.comment 
                                                                    INNER JOIN
                                                                        qma.users
                                                                    ON
                                                                        users.id = comment.User_ID
                                                                    WHERE 
                                                                        comment.Project_ID = ?
                                                                    ORDER BY 
                                                                        comment.id DESC");

                                            $stmt->execute(array($row['id']));
                                            $comment = $stmt->fetch();

                                            echo "<td class='max-comment'>";
                                                if (empty($comment['Comment'])) {

                                                    echo "لايوجد";
                                
                                                } else {

                                                    echo "<i class='fa fa-bell'></i>";

                                                    echo "<span><b>". $comment['Username'] . ":</b> " . $comment['Comment'] . "<hr> <a class='btn btn-primary' href='tasks.php?do=detials&id=" . $row['id'] . "'><i class='fa fa-plus'></i> إضافة تعليق</a></span>";

                                                }

                                            echo "</td>";
                                            echo "</td>";
                                            echo "<td><a href='members.php?do=about&id=" . $row['Member'] . "'>" . $row['Username'] . "</a></td>";
                                            echo "<td>";
                                            echo "<a class='btn btn-success mb-1' href='tasks.php?do=detials&id=" . $row['id'] . "'><i class='fa fa-info'></i></a> ";

                                                if ($row["Member"] == $id || $adminCheck > 0) {

                                                    echo "<a class='btn btn-info mb-1 text-light' href='tasks.php?do=edit&id=" . $row['id'] . "'><i class='fa fa-edit'></i></a> ";

                                                } 

                                                if ($adminCheck > 0) {
                                
                                                    echo "<a class='btn btn-danger confirm mb-1' href='tasks.php?do=delete&id=" . $row['id'] . "'><i class='fa fa-close'></i></a>";

                                                }
                                                
                                            echo "</td>";
                                        echo "</tr>";
                                    }
                                            ?>
                            </tbody>
                        </table>
                    </div>
                    <?php

                        if ($adminCheck > 0) {
                            ?>

                                <a href="tasks.php?do=add" class="btn btn-primary"><i class='fa fa-plus'></i> إضافة معاملة</a>

                            <?php
                        }
                    ?>
                </div>
            <?php

        } elseif ($do == "detials") {

            $_SESSION['project_id'] = $_GET['id'];
            $projectIdSession = $_SESSION['project_id'];

            $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE id = ? AND Group_ID = 1 OR id = ? AND Group_ID = 2");
            $stmt->execute(array($_SESSION['id'], $_SESSION['id']));
            $adminCheck = $stmt->rowCount();
            
            $stmt = $con->prepare("SELECT id FROM qma.projects WHERE id = ?");
            $stmt->execute(array($_GET['id']));
            $count = $stmt->rowCount();

            if ($count > 0) {

                $stmt = $con->prepare(" SELECT 
                                            projects.*,
                                            users.Username
                                        FROM 
                                            qma.projects
                                        INNER JOIN
                                            qma.users
                                        ON
                                            users.id = projects.Member
                                        WHERE 
                                            projects.id = ?");
                $stmt->execute(array($_GET['id']));
                $row = $stmt->fetch();

                ?>

                    <div class="container">
                        <h2 class="text-center text-primary mt-5 mb-4">تفاصيل المعاملة رقم <?php echo $row['id']; ?> <?php echo $row['Completed'] == 1 ? "<i class='fa-solid fa-check' style='color: #00ff2a;'></i>" : "" ?></h2>
                        <ul class="list-group p-0">
                            <li class="list-group-item"><span class='custom-span'>رقم المعاملة: </span><?php echo $row['id']; ?></li>
                            <li class="list-group-item"><span class='custom-span'>إسم العميل أو الشركة: </span><?php echo $row['Client_Name']; ?></li>
                            <li class="list-group-item"><span class='custom-span'>تاريخ إستلام المعاملة: </span><?php echo $row['Date']; ?></li>
                            <li class="list-group-item"><span class='custom-span'>عدد الوحدات: </span><?php echo $row['Units_Number'] == 0 ? "لم يحدد" : $row['Units_Number']; ?></li>
                            <li class="list-group-item"><span class='custom-span'>رابط المعاملة: </span><?php echo empty($row['Project_Link']) ? "لم يحدد" : $row['Project_Link']; ?></li>
                            <li class="list-group-item"><span class='custom-span'>نوع المعاملة: </span><?php echo $row['Project_Task'] == 1 ? "مبدئي" : ($row['Project_Task'] == 2 ? "مميز" : ($row['Project_Task'] == 3 ? "نهائي" : ($row['Project_Task'] == 4 ? "مميز ونهائي"  : "لم يحدد"))) ; ?></li>
                            <li class="list-group-item"><span class='custom-span'>إستلام ملف الكاد: </span><?php echo $row['CAD'] == 0 ? "لا" : "نعم";?></li>
                            <li class="list-group-item"><span class='custom-span'>إستلام الرخصة: </span><?php echo $row['Rkhsa'] == 0 ? "لا" : "نعم";?></li>
                            <li class="list-group-item"><span class='custom-span'>إستلام الصك: </span><?php echo $row['Sak'] == 0 ? "لا" : "نعم";?></li>
                            <li class="list-group-item"><span class='custom-span'>تحديد مكان غرفة العدادات: </span><?php echo $row['CNR'] == 0 ? "لا" : "نعم";?></li>
                            <li class="list-group-item"><span class='custom-span'>تحديد غرفة الكهرباء: </span><?php echo $row['ECR'] == 0 ? "لا" : "نعم";?></li>
                            <li class="list-group-item"><span class='custom-span'>نتائج تشييك مساحات الفلور: </span><?php echo empty($row['Floor']) ? "لم يحدد" : $row['Floor']; ?></li>
                            <li class="list-group-item"><span class='custom-span'>تشييك ملف HTML: </span><?php echo $row['HTML'] == 0 ? "لا" : "نعم";?></li>
                            <li class="list-group-item"><span class='custom-span'>مراجعة المخاطر: </span><?php echo $row['Risk'] == 0 ? "لا" : "نعم";?></li>
                            <li class="list-group-item"><span class='custom-span'>فرز مبدئي: </span><?php echo $row['Primary_Sort'] == 0 ? "لا" : "نعم";?></li>
                            <li class="list-group-item"><span class='custom-span'>إكمال GIS: </span><?php echo $row['GIS'] == 0 ? "لا" : "نعم";?></li>
                            <li class="list-group-item"><span class='custom-span'>إكمال EXCEL: </span><?php echo $row['EXCEL'] == 0 ? "لا" : "نعم";?></li>
                            <li class="list-group-item"><span class='custom-span'>صورة الوحدة والدور: </span><?php echo $row['WD'] == 0 ? "لا" : "نعم";?></li>
                            <li class="list-group-item"><span class='custom-span'>مخططات توضيحية: </span><?php echo $row['MT'] == 0 ? "لا" : "نعم";?></li>
                            <li class="list-group-item"><span class='custom-span'>وثيقة: </span><?php echo $row['W'] == 0 ? "لا" : "نعم";?></li>
                            <li class="list-group-item"><span class='custom-span'>تنفيذ الأعمال: </span><a href="members.php?do=about&id=<?php echo $row['Member']; ?>"><?php echo $row['Username']; ?></a></li>
                            <?php
                            ?>
                            <div class="box mt-3">
                                <?php
                                
                                    if ($row["Member"] == $id || $adminCheck > 0) {

                                        ?>

                                            <a class='btn btn-info text-light' href='tasks.php?do=edit&id=<?php echo $row['id']?>'><i class='fa fa-edit'></i> تعديل</a>
                                        
                                        <?php

                                    } 
                                    if ($adminCheck > 0) {

                                        ?>
                                            <a class='btn btn-danger confirm' href='tasks.php?do=delete&id=<?php echo $row['id']?>'><i class='fa fa-close'></i> حذف</a>
                                        <?php
                                    } 
                                ?>
                            </div>
                            <hr>
                            <form action="tasks.php?do=insertcomment" method="POST">
                                <input type="hidden" name="user_id" value="<?php echo $_SESSION['id']; ?>">
                                <input type="hidden" name="project_id" value="<?php echo $row['id']; ?>">
                                <label for="comment mb-3">إضافة تعليق</label>
                                <div class="form-group mb-3">
                                    <textarea class="form-control" name="comment" id="comment" rows="4" placeholder="أكتب ملاحظة أو تعليق"></textarea>
                                </div>
                                <input class='btn btn-primary mb-4' type="submit" value="إضافة تعليق">
                            </form>
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
                                                        WHERE 
                                                            comment.Project_ID = ?
                                                        ORDER BY comment.id DESC");

                                $stmt->execute(array($row['id']));
                                $count = $stmt->rowCount();
                                $rows = $stmt->fetchAll();

                                $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE (id = ? AND Group_ID = 1) OR (id = ? AND Group_ID = 2)");
                                $stmt->execute(array($id, $id));
                                $adminCheck = $stmt->rowCount();
                                
                                if ($count > 0) {

                                    foreach ($rows as $row) {

                                        echo "<div>";
                                            echo "<span class='comment-user'>" . $row['Username'] . ": </span>";
                                            echo "<span class='comment-body'>" . $row['Comment'];

                                            if ($adminCheck > 0) {

                                                echo " <a href='tasks.php?do=deletecomment&id=" . $row['id'] . "' class='btn btn-danger text-light pull-left confirm'><i class='fa fa-close'></i></a>";

                                            }
                                            
                                            if ($row['User_ID'] == $_SESSION['id']) {

                                                echo " <a href='tasks.php?do=editcomment&id=" . $row['id'] . "' class='btn btn-info text-light pull-left'><i class='fa fa-edit'></i></a> ";
                                            }

                                            echo "</span>";
                                            echo "<span class='pull-left comment-date'>" . $row['Date'] . "</span>";
                                        echo "</div>";
                                        echo "<hr>";
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
                    echo "<div class='alert alert-warning mt-5'>المعاملة غير موجودة</div>";
                    if ($adminCheck > 0) {
                        echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة إدارة المعاملات بعد 5 ثوان</div>";
                    } else {
                        echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة المعاملات بعد 5 ثوان</div>";
                    }
                header("refresh:5;url=tasks.php");
                exit();
                echo "</div>";
                
            }

        } elseif ($do == "add") {

            $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE id = ? AND Group_ID = 1 OR id = ? AND Group_ID = 2");
            $stmt->execute(array($_SESSION['id'], $_SESSION['id']));
            $adminCheck = $stmt->rowCount();

            if ($adminCheck > 0) {
                $stmt = $con->prepare(" SELECT
                                            *
                                        FROM 
                                            qma.users
                                        WHERE
                                            users.Group_ID = 0 OR users.Group_ID = 2");
                $stmt->execute();
                $rows = $stmt->fetchAll();

                ?>
                    <div class="container">
                    <h2 class="text-primary text-center mt-5 mb-4">إضافة معاملة</h2>
                        <form class='w-50 m-auto' action="tasks.php?do=insert" method="POST">
                            <div class="form-group mb-3">
                                <label for="id">رقم المعاملة</label>
                                <input type="number" class="form-control-plaintext" id="id" name="id" readonly>
                            </div>

                            <div class="form-group mb-3 position-relative">
                                <label for="client-name">إسم الشركة أو العميل</label>
                                <input type="text" class="form-control" name="client-name" id="client-name" placeholder="أدخل إسم الشركة أو العميل" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="units-number">عدد الوحدات</label>
                                <input type="number" class="form-control" name="units-number" id="units-number" placeholder="أدخل عدد الوحدات">
                            </div>
                            <div class="form-group mb-3">
                                <label for="project-link">رابط المعاملة</label>
                                <input type="text" class="form-control" name="project-link" id="project-link" placeholder="أدخل رابط المعاملة">
                            </div>
                            <label for="comment">إجراءات وملاحظات</label>
                            <div class="form-group mb-3">
                                <textarea class="form-control" name="comment" id="comment" rows="4" cols="50" placeholder="أكتب ملاحظة أو تعليق"></textarea>
                            </div>
                            <div class="form-group mb-3 position-relative">
                                <label for="project-task mb-2">نوع المعاملة</label>
                                <select class="form-select" name="project-task" id="project-task" required>
                                    <option value="0">...</option>
                                    <option value="1">مبدئي</option>
                                    <option value="2">مميز</option>
                                    <option value="3">نهائي</option>
                                    <option value="4">مبدئي ونهائي</option>
                                </select>
                            </div>

                            <label class="mb-2">إستلام ملف الكاد</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cad" value="1" id="cad-yes">
                                <label class="form-check-label" for="cad-yes">
                                    نعم
                                </label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="cad" value="0" id="cad-no" checked>
                                <label class="form-check-label" for="cad-no">
                                    لا
                                </label>
                            </div>

                            <label class="mb-2">إستلام الرخصة</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rkhsa" value="1" id="rkhsa-yes">
                                <label class="form-check-label" for="rkhsa-yes">
                                    نعم
                                </label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="rkhsa" value="0" id="rkhsa-no" checked>
                                <label class="form-check-label" for="rkhsa-no">
                                    لا
                                </label>
                            </div>

                            <label class="mb-2">إستلام الصك</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sak" value="1" id="sak-yes">
                                <label class="form-check-label" for="sak-yes">
                                    نعم
                                </label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="sak" value="0" id="sak-no" checked>
                                <label class="form-check-label" for="sak-no">
                                    لا
                                </label>
                            </div>

                            <label class="mb-2">تحديد مكان غرفة العدادات</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cnr" value="1" id="cnr-yes">
                                <label class="form-check-label" for="cnr-yes">
                                    نعم
                                </label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="cnr" value="0" id="cnr-no" checked>
                                <label class="form-check-label" for="cnr-no">
                                    لا
                                </label>
                            </div>

                            <label class="mb-2">تحديد غرفة الكهرباء </label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="ecr" value="1" id="ecr-yes">
                                <label class="form-check-label" for="ecr-yes">
                                    نعم
                                </label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="ecr" value="0" id="ecr-no" checked>
                                <label class="form-check-label" for="ecr-no">
                                    لا
                                </label>
                            </div>

                            <label for="floor">نتائج تشييك مساحات الفلور</label>
                            <div class="form-group mb-3">
                                <textarea class="form-control" name="floor" id="floor" rows="4" cols="50" placeholder="أكتب بالتفصيل"></textarea>
                            </div>

                            <label class="mb-2"> تشييك ملف HTML </label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="html" value="1" id="html-yes">
                                <label class="form-check-label" for="html-yes">
                                    نعم
                                </label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="html" value="0" id="html-no" checked>
                                <label class="form-check-label" for="html-no">
                                    لا
                                </label>
                            </div>

                            <label class="mb-2">مراجعة المخاطر</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="risk" value="1" id="risk-yes">
                                <label class="form-check-label" for="risk-yes">
                                    نعم
                                </label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="risk" value="0" id="risk-no" checked>
                                <label class="form-check-label" for="risk-no">
                                    لا
                                </label>
                            </div>

                            <label class="mb-2">فرز مبدئي</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="primary-sort" value="1" id="primary-sort-yes">
                                <label class="form-check-label" for="primary-sort-yes">
                                    نعم
                                </label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="primary-sort" value="0" id="primary-sort-no" checked>
                                <label class="form-check-label" for="primary-sort-no">
                                    لا
                                </label>
                            </div>

                            <label class="mb-2">GIS</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gis" value="1" id="gis-yes">
                                <label class="form-check-label" for="gis-sort-yes">
                                    نعم
                                </label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="gis" value="0" id="gis-no" checked>
                                <label class="form-check-label" for="gis-no">
                                    لا
                                </label>
                            </div>

                            <label class="mb-2">EXCEL</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="excel" value="1" id="excel-yes">
                                <label class="form-check-label" for="excel-yes">
                                    نعم
                                </label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="excel" value="0" id="excel-no" checked>
                                <label class="form-check-label" for="excel-no">
                                    لا
                                </label>
                            </div>

                            <label class="mb-2">صورة الوحدة والدور</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="wd" value="1" id="wd-yes">
                                <label class="form-check-label" for="wd-yes">
                                    نعم
                                </label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="wd" value="0" id="wd-no" checked>
                                <label class="form-check-label" for="wd-no">
                                    لا
                                </label>
                            </div>

                            <label class="mb-2">مخخطات توضيحية</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mt" value="1" id="mt-yes">
                                <label class="form-check-label" for="mt-yes">
                                    نعم
                                </label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="mt" value="0" id="mt-no" checked>
                                <label class="form-check-label" for="mt-no">
                                    لا
                                </label>
                            </div>

                            <label class="mb-2">وثيقة</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="w" value="1" id="w-yes">
                                <label class="form-check-label" for="w-yes">
                                    نعم
                                </label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="w" value="0" id="w-no" checked>
                                <label class="form-check-label" for="w-no">
                                    لا
                                </label>
                            </div>
                            <div class="form-group mb-3 position-relative">
                                <label class="mb-2">تنفيذ الأعمال</label>
                                <select class="form-select mb-4 position-relative" name="member" id="member" required>
                                    <option value="0">...</option>
                                    <?php

                                        foreach ($rows as $row) {
                                            echo "<option value=" . $row['id'] . ">" . $row['Username'] . "</option>";
                                        }
                            
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">حفظ</button>
                        </form>

                    </div>
                
                <?php

            } else {


                echo "<div class='container'>";
                    echo "<div class='alert alert-warning mt-5'>غير مصرح لك بالدخول إلى هذا الرابط</div>";
                    echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة المعاملات بعد 5 ثوان</div>";
                    header("refresh:5;url=tasks.php");
                    exit();
                echo "</div>";

            }





        } elseif ($do == "insert") {
            
            if ($_SERVER['REQUEST_METHOD'] == "POST") {

                $projectId = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
                $clientName = filter_var($_POST['client-name'], FILTER_SANITIZE_STRING);
                $unitsNumber = filter_var($_POST['units-number'], FILTER_SANITIZE_NUMBER_INT);
                $projectLink = filter_var($_POST['project-link'], FILTER_SANITIZE_STRING);
                $comment = filter_var($_POST['comment'], FILTER_SANITIZE_STRING);
                $projectTast = filter_var($_POST['project-task'], FILTER_SANITIZE_NUMBER_INT);
                $cad = filter_var($_POST['cad'], FILTER_SANITIZE_NUMBER_INT);
                $rkhsa = filter_var($_POST['rkhsa'], FILTER_SANITIZE_NUMBER_INT);
                $sak = filter_var($_POST['sak'], FILTER_SANITIZE_NUMBER_INT);
                $cnr = filter_var($_POST['cnr'], FILTER_SANITIZE_NUMBER_INT);
                $ecr = filter_var($_POST['ecr'], FILTER_SANITIZE_NUMBER_INT);
                $floor = filter_var($_POST['floor'], FILTER_SANITIZE_STRING);
                $html = filter_var($_POST['html'], FILTER_SANITIZE_NUMBER_INT);
                $risk = filter_var($_POST['risk'], FILTER_SANITIZE_NUMBER_INT);
                $primarySort = filter_var($_POST['primary-sort'], FILTER_SANITIZE_NUMBER_INT);
                $gis = filter_var($_POST['gis'], FILTER_SANITIZE_NUMBER_INT);
                $excel = filter_var($_POST['excel'], FILTER_SANITIZE_NUMBER_INT);
                $wd = filter_var($_POST['wd'], FILTER_SANITIZE_NUMBER_INT);
                $mt = filter_var($_POST['mt'], FILTER_SANITIZE_NUMBER_INT);
                $w = filter_var($_POST['w'], FILTER_SANITIZE_NUMBER_INT);
                $member = filter_var($_POST['member'], FILTER_SANITIZE_STRING);

                $formErrors = [];

                if (empty($clientName)) {
                    $formErrors[] = "لايمكن ترك حقل اسم الشركة أو العميل فارغا";
                }
                if (empty($projectTast)) {
                    $formErrors[] = "لايمكن ترك حقل نوع المعاملة فارغا";
                }
                if (empty($member)) {
                    $formErrors[] = "لايمكن ترك حقل منفذ المعاملة  فارغا";
                }

                if (!empty($formErrors)) {
                        echo "<div class='container mt-5'>";
                            foreach ($formErrors as $error) {
                                    echo "<div class='alert alert-danger'>" . $error . "</div>";
                            }
                            echo "<div class='alert alert-info'>سيتم تحديث الصفحة بعد 5 ثواني</div>";
                            header("refresh:5; url=tasks.php?do=add");
                            exit();
                        echo "</div>";
                } else {

                    $stmt = $con->prepare("INSERT INTO 
                    qma.projects (id,
                                         Client_Name,
                                          Date, 
                                          Units_Number, 
                                          Project_Link, 
                                          Comments, 
                                          Project_Task, 
                                          CAD, 
                                          Rkhsa, 
                                          Sak, 
                                          CNR, 
                                          ECR, 
                                          Floor, 
                                          HTML, 
                                          Risk, 
                                          Primary_Sort, 
                                          GIS, 
                                          EXCEL, 
                                          WD, 
                                          MT, 
                                          W, 
                                          Member) 
                                VALUES (?, ?, now(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute(array($projectId, $clientName, $unitsNumber, $projectLink, $comment, $projectTast, $cad, $rkhsa, $sak, $cnr, $ecr, $floor, $html, $risk, $primarySort, $gis, $excel, $wd, $mt, $w, $member));
                    $count = $stmt->rowCount();
        
                    if ($count > 0) {
        
                    echo "<div class='container'>";
                        echo "<div class='alert alert-success mt-5'>تم إضافة معاملة جديدة بنجاح</div>";
                        echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة إدارة المعاملات بعد 5 ثواني</div>";
                        header("refresh:5; url=tasks.php");
                        exit();
                    echo "</div>";


        
                    } else {
        
                    echo "<div class='container'>";
                        echo "<div class='alert alert-danger mt-5'>حدث خطأ حاول مرة أخرى</div>";
                        echo "<div class='alert alert-info'>سيتم تحديث الصفحة بعد 5 ثواني</div>";
                        header("refresh:5; url=tasks.php?do=add");
                        exit();
                    echo "</div>";


                    }

                }

            }
            
        } elseif ($do == "edit") {

            $_SESSION['project_id'] = $_GET['id'];

            $projectIdSession = $_SESSION['project_id'];

            $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE id = ? AND Group_ID = 1 OR id = ? AND Group_ID = 2");
            $stmt->execute(array($_SESSION['id'], $_SESSION['id']));
            $adminCheck = $stmt->rowCount();

            $stmt = $con->prepare("SELECT 
                                        projects.id AS Project_ID,
                                        users.id,
                                        users.Group_ID
                                        
                                    FROM 
                                        qma.users 
                                    INNER JOIN
                                        qma.projects
                                    ON
                                        projects.Member = users.id
                                    WHERE  
                                        (users.Group_ID = 1 OR users.Group_ID = 2)
                                    OR
                                        (projects.Member = ? AND projects.id = ?)");
                                        
            $stmt->execute(array($id, $_GET['id']));
            $row = $stmt->fetch();
            $allows = $stmt->rowCount();

            if ($allows > 0 || $adminCheck > 0) {

                $stmt = $con->prepare("SELECT * FROM qma.projects WHERE id = ?");
                $stmt->execute(array($_GET['id']));
                $count = $stmt->rowCount();
                $row = $stmt-> fetch();
    
                if ($count > 0) {
    
                    ?>
                        <div class="container">
                            <h2 class="text-primary text-center mt-5 mb-4">تعديل المعاملة</h2>
                            <form class='w-50 m-auto' action="tasks.php?do=update" method="POST">
                                <div class="form-group mb-3">
                                    <label for="id">رقم المعاملة</label>
                                    <input type="number" class="form-control-plaintext" id="id"  value="<?php echo $row['id']; ?>" readonly>
                                </div>
                                <?php
                                    if ($adminCheck > 0) {
                                        ?>
                                            <div class="form-group mb-3 position-relative">
                                                <label for="client-name">إسم الشركة أو العميل</label>
                                                <input type="text" class="form-control" name="client-name" id="client-name" value="<?php echo $row['Client_Name'] ?>" placeholder="أدخل إسم الشركة أو العميل" required>
                                            </div>
                                            <label class="mb-2">حالة المعاملة</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="completed" id="completed-yes" <?php echo $row['Completed'] == 1 ? "checked" : ""?> value="1">
                                                <label class="form-check-label" for="completed-yes">
                                                    مكتملة
                                                </label>
                                            </div>
                                            <div class="form-check mb-4">
                                                <input class="form-check-input" type="radio" name="completed" id="completed-no" <?php echo $row['Completed'] == 0 ? "checked" : ""?> value="0">
                                                <label class="form-check-label" for="completed-no">
                                                    غير مكتملة
                                                </label>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label for="units-number">عدد الوحدات</label>
                                                <input type="number" class="form-control" name="units-number" id="units-number" value="<?php echo $row['Units_Number'] ?>" placeholder="أدخل عدد الوحدات">
                                            </div>
                                        <?php
                                    } else {
                                        ?>
                                            <div class="form-group mb-3 position-relative">
                                                <label for="client-name">إسم الشركة أو العميل</label>
                                                <input type="text" class="form-control" name="client-name" id="client-name" value="<?php echo $row['Client_Name'] ?>" placeholder="إسم الشركة أو العميل" readonly>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label for="units-number">عدد الوحدات</label>
                                                <input type="number" class="form-control" name="units-number" id="units-number" value="<?php echo $row['Units_Number'] ?>" placeholder="عدد الوحدات" readonly>
                                            </div>
                                        <?php
                                    }
                                ?>
                            
                                <?php
                                    if ($adminCheck > 0) {
                                        ?>
                                            <div class="form-group mb-3">
                                                <label for="project-link">رابط المعاملة</label>
                                                <input type="text" class="form-control" name="project-link" id="project-link" value="<?php echo $row['Project_Link'] ?>" placeholder="أدخل رابط المعاملة">
                                            </div>
                                        <?php
                                    } else {

                                        ?>
                                            <div class="form-group mb-3">
                                                <label for="project-link">رابط المعاملة</label>
                                                <input type="text" class="form-control" name="project-link" id="project-link" value="<?php echo $row['Project_Link'] ?>" placeholder="إنسخ رابط المعاملة" readonly>
                                            </div>
                                        <?php
                                        
                                    }
                                ?>
                                <label for="comment">إجراءات وملاحظات</label>
                                <div class="form-group mb-3">
                                    <textarea class="form-control" name="comment" id="comment" rows="4" cols="50" placeholder="أكتب ملاحظة أو تعليق"><?php echo $row['Comments'] ?></textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="project-task">نوع المعاملة</label>
                                    <select class="form-control" name="project-task" id="project-task" required>
                                        <option <?php echo $row['Project_Task'] == 0 ? "selected" : ""?> value="0">...</option>
                                        <option <?php echo $row['Project_Task'] == 1 ? "selected" : ""?> value="1">مبدئي</option>
                                        <option <?php echo $row['Project_Task'] == 2 ? "selected" : ""?> value="2">مميز</option>
                                        <option <?php echo $row['Project_Task'] == 3 ? "selected" : ""?> value="3">نهائي</option>
                                        <option <?php echo $row['Project_Task'] == 4 ? "selected" : ""?> value="4">مبدئي ونهائي</option>
                                    </select>
                                </div>
    
                                <label class="mb-2">إستلام ملف الكاد</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="cad" id="cad-yes" <?php echo $row['CAD'] == 1 ? "checked" : ""?> value="1">
                                    <label class="form-check-label" for="cad-yes">
                                        نعم
                                    </label>
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="radio" name="cad" id="cad-no" <?php echo $row['CAD'] == 0 ? "checked" : ""?> value="0">
                                    <label class="form-check-label" for="cad-no">
                                        لا
                                    </label>
                                </div>
    
                                <label class="mb-2">إستلام الرخصة</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="rkhsa" id="rkhsa-yes <?php echo $row['Rkhsa'] == 1 ? "checked" : ""?>" value="1">
                                    <label class="form-check-label" for="rkhsa-yes">
                                        نعم
                                    </label>
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="radio" name="rkhsa" id="rkhsa-no" <?php echo $row['Rkhsa'] == 0 ? "checked" : ""?> value="0">
                                    <label class="form-check-label" for="rkhsa-no">
                                        لا
                                    </label>
                                </div>
    
                                <label class="mb-2">إستلام الصك</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="sak" id="sak-yes <?php echo $row['Sak'] == 1 ? "checked" : ""?>" value="1">
                                    <label class="form-check-label" for="sak-yes">
                                        نعم
                                    </label>
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="radio" name="sak" id="sak-no" <?php echo $row['Sak'] == 0 ? "checked" : ""?> value="0">
                                    <label class="form-check-label" for="sak-no">
                                        لا
                                    </label>
                                </div>
    
                                <label class="mb-2">تحديد مكان غرفة العدادات</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="cnr" id="cnr-yes" <?php echo $row['CNR'] == 1 ? "checked" : ""?> value="1">
                                    <label class="form-check-label" for="cnr-yes">
                                        نعم
                                    </label>
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="radio" name="cnr" id="cnr-no" <?php echo $row['CNR'] == 0 ? "checked" : ""?> value="0">
                                    <label class="form-check-label" for="cnr-no">
                                        لا
                                    </label>
                                </div>
    
                                <label class="mb-2">تحديد غرفة الكهرباء </label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="ecr" id="ecr-yes" <?php echo $row['ECR'] == 1 ? "checked" : ""?> value="1">
                                    <label class="form-check-label" for="ecr-yes">
                                        نعم
                                    </label>
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="radio" name="ecr" id="ecr-no" <?php echo $row['ECR'] == 0 ? "checked" : ""?> value="0">
                                    <label class="form-check-label" for="ecr-no">
                                        لا
                                    </label>
                                </div>
    
                                <label for="floor">نتائج تشييك مساحات الفلور</label>
                                <div class="form-group mb-3">
                                    <textarea class="form-control" name="floor" id="floor" rows="4" cols="50" placeholder="أكتب بالتفصيل"><?php echo $row['Floor'] ?></textarea>
                                </div>
    
                                <label class="mb-2"> تشييك ملف HTML </label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="html" id="html-yes" <?php echo $row['HTML'] == 1 ? "checked" : ""?> value="1">
                                    <label class="form-check-label" for="html-yes">
                                        نعم
                                    </label>
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="radio" name="html" id="html-no" <?php echo $row['HTML'] == 0 ? "checked" : ""?> value="0">
                                    <label class="form-check-label" for="html-no">
                                        لا
                                    </label>
                                </div>
    
                                <label class="mb-2">مراجعة المخاطر</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="risk" id="risk-yes" <?php echo $row['Risk'] == 1 ? "checked" : ""?> value="1">
                                    <label class="form-check-label" for="risk-yes">
                                        نعم
                                    </label>
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="radio" name="risk" id="risk-no" <?php echo $row['Risk'] == 0 ? "checked" : ""?> value="0">
                                    <label class="form-check-label" for="risk-no">
                                        لا
                                    </label>
                                </div>
    
                                <label class="mb-2">فرز مبدئي</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="primary-sort" id="primary-sort-yes" <?php echo $row['Primary_Sort'] == 1 ? "checked" : ""?> value="1">
                                    <label class="form-check-label" for="primary-sort-yes">
                                        نعم
                                    </label>
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="radio" name="primary-sort" id="primary-sort-no" <?php echo $row['Primary_Sort'] == 0 ? "checked" : ""?> value="0">
                                    <label class="form-check-label" for="primary-sort-no">
                                        لا
                                    </label>
                                </div>
    
                                <label class="mb-2">GIS</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gis" id="gis-yes" <?php echo $row['GIS'] == 1 ? "checked" : ""?> value="1">
                                    <label class="form-check-label" for="gis-sort-yes">
                                        نعم
                                    </label>
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="radio" name="gis" id="gis-no" <?php echo $row['GIS'] == 0 ? "checked" : ""?> value="0">
                                    <label class="form-check-label" for="gis-no">
                                        لا
                                    </label>
                                </div>
    
                                <label class="mb-2">EXCEL</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="excel" id="excel-yes" <?php echo $row['EXCEL'] == 1 ? "checked" : ""?> value="1">
                                    <label class="form-check-label" for="excel-yes">
                                        نعم
                                    </label>
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="radio" name="excel" id="excel-no" <?php echo $row['EXCEL'] == 0 ? "checked" : ""?> value="0">
                                    <label class="form-check-label" for="excel-no">
                                        لا
                                    </label>
                                </div>
    
                                <label class="mb-2">صورة الوحدة والدور</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="wd" id="wd-yes" <?php echo $row['WD'] == 1 ? "checked" : ""?> value="1">
                                    <label class="form-check-label" for="wd-yes">
                                        نعم
                                    </label>
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="radio" name="wd" id="wd-no" <?php echo $row['WD'] == 0 ? "checked" : ""?> value="0">
                                    <label class="form-check-label" for="wd-no">
                                        لا
                                    </label>
                                </div>
    
                                <label class="mb-2">مخخطات توضيحية</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mt" id="mt-yes" <?php echo $row['MT'] == 1 ? "checked" : ""?> value="1">
                                    <label class="form-check-label" for="mt-yes">
                                        نعم
                                    </label>
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="radio" name="mt" id="mt-no" <?php echo $row['MT'] == 0 ? "checked" : ""?> value="0">
                                    <label class="form-check-label" for="mt-no">
                                        لا
                                    </label>
                                </div>
    
                                <label class="mb-2">وثيقة</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="w" id="w-yes" <?php echo $row['W'] == 1 ? "checked" : ""?> value="1">
                                    <label class="form-check-label" for="w-yes">
                                        نعم
                                    </label>
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="radio" name="w" id="w-no" <?php echo $row['W'] == 0 ? "checked" : ""?> value="0">
                                    <label class="form-check-label" for="w-no">
                                        لا
                                    </label>
                                </div>
                                <?php
                                    if ($adminCheck > 0) {
                                        ?>
                                            <div class="form-group mb-3 position-relative">
                                                <label class="mb-2">تنفيذ الأعمال</label>
                                                <select class="form-select mb-4 position-relative" name="member" id="member" required>
                                                    <option value="0">...</option>
                                                    <?php
                
                                                        $stmt = $con->prepare(" SELECT
                                                                                    *
                                                                                FROM 
                                                                                    qma.users
                                                                                WHERE
                                                                                    users.Group_ID = 0 OR users.Group_ID = 2");
                                                        $stmt->execute();
                                                        $rows2 = $stmt->fetchAll();
                
                                                        foreach ($rows2 as $row2) {
                                                            ?>
                                                                <option value='<?php echo $row2['id'];?>'<?php echo $row['Member'] == $row2['id'] ? 'selected' : '' ?>> <?php echo $row2['Username']; ?></option>
                                                            <?php
                                                        }
                                            
                                                    ?>
                                                </select>
                                            </div>
                                        <?php
                                    } else {


                                        ?>
                                            <div class="form-group mb-3 position-relative">
                                                <label class="mb-2">تنفيذ الأعمال</label>
                                                <select class="form-select mb-4 position-relative" name='member' id="member">
                                                    <option value='<?php echo $_SESSION['id'];?>' selected ><?php echo $_SESSION['user']; ?></option>                                                        
                                                </select>
                                            </div>
                                        <?php

                                    }
                                ?>

                                <button type="submit" class="btn btn-primary">تحديث</button>
                            </form>
                        </div>
    
                    <?php
    
                } else {
    
                    echo "<div class='container'>";
                        echo "<div class='alert alert-warning mt-5'>المعاملة غير موجودة</div>";
                        echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة إدارة المعاملات بعد 5 ثوان</div>";
                        header("refresh:5;url=tasks.php");
                        exit();
                    echo "</div>";
                    
                }

            } else {

                echo "<div class='container'>";
                    echo "<div class='alert alert-warning mt-5'>غير مصرح لك بالدخول إلى هذا الرابط</div>";
                    echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة المعاملات بعد 5 ثوان</div>";
                    header("refresh:5;url=tasks.php");
                    exit();
                echo "</div>";
                
            }

        } elseif ($do == "update") {

            if ($_SERVER['REQUEST_METHOD'] == "POST") {

                $projectIdSession = $_SESSION['project_id'];

                $projectId = $projectIdSession;
                $clientName = filter_var($_POST['client-name'], FILTER_SANITIZE_STRING);
                $completed = filter_var($_POST['completed'], FILTER_SANITIZE_NUMBER_INT);
                $unitsNumber = filter_var($_POST['units-number'], FILTER_SANITIZE_NUMBER_INT);
                $projectLink = filter_var($_POST['project-link'], FILTER_SANITIZE_STRING);
                $comment = filter_var($_POST['comment'], FILTER_SANITIZE_STRING);
                $projectTask = filter_var($_POST['project-task'], FILTER_SANITIZE_NUMBER_INT);
                $cad = filter_var($_POST['cad'], FILTER_SANITIZE_NUMBER_INT);
                $rkhsa = filter_var($_POST['rkhsa'], FILTER_SANITIZE_NUMBER_INT);
                $sak = filter_var($_POST['sak'], FILTER_SANITIZE_NUMBER_INT);
                $cnr = filter_var($_POST['cnr'], FILTER_SANITIZE_NUMBER_INT);
                $ecr = filter_var($_POST['ecr'], FILTER_SANITIZE_NUMBER_INT);
                $floor = filter_var($_POST['floor'], FILTER_SANITIZE_STRING);
                $html = filter_var($_POST['html'], FILTER_SANITIZE_NUMBER_INT);
                $risk = filter_var($_POST['risk'], FILTER_SANITIZE_NUMBER_INT);
                $primarySort = filter_var($_POST['primary-sort'], FILTER_SANITIZE_NUMBER_INT);
                $gis = filter_var($_POST['gis'], FILTER_SANITIZE_NUMBER_INT);
                $excel = filter_var($_POST['excel'], FILTER_SANITIZE_NUMBER_INT);
                $wd = filter_var($_POST['wd'], FILTER_SANITIZE_NUMBER_INT);
                $mt = filter_var($_POST['mt'], FILTER_SANITIZE_NUMBER_INT);
                $w = filter_var($_POST['w'], FILTER_SANITIZE_NUMBER_INT);
                $member = filter_var($_POST['member'], FILTER_SANITIZE_STRING);



                $formErrors = [];

                if (empty($clientName)) {
                    $formErrors[] = "لايمكن ترك حقل اسم الشركة أو العميل فارغا";
                }
                if (empty($member)) {
                    $formErrors[] = "لايمكن ترك حقل منفذ المعاملة  فارغا";
                }

                if (!empty($formErrors)) {
                        echo "<div class='container mt-5'>";
                    foreach ($formErrors as $error) {
                            echo "<div class='alert alert-danger'>" . $error . "</div>";
                    }
                        echo "</div>";
                } else {

                    $stmt = $con->prepare("SELECT id, Group_ID FROM qma.users WHERE (id = ? AND Group_ID = 1) OR (id = ? AND Group_ID = 2)");
                    $stmt->execute(array($_SESSION['id'], $_SESSION['id']));
                    $adminCheck = $stmt->rowCount();

                    if ($adminCheck > 0) {

                        $stmt = $con->prepare("UPDATE qma.projects SET 
                                                        Client_Name = ?,
                                                        Date = now(), 
                                                        Units_Number = ?, 
                                                        Project_Link = ?, 
                                                        Comments = ?, 
                                                        Project_Task = ?, 
                                                        CAD = ?, 
                                                        Rkhsa = ?, 
                                                        Sak = ?, 
                                                        CNR = ?, 
                                                        ECR = ?, 
                                                        Floor = ?, 
                                                        HTML = ?, 
                                                        Risk = ?, 
                                                        Primary_Sort = ?, 
                                                        GIS = ?, 
                                                        EXCEL = ?, 
                                                        WD = ?, 
                                                        MT = ?, 
                                                        W = ?, 
                                                        Completed = ?,
                                                        Member = ? 
                                                WHERE 
                                                        id = ?");
                        $stmt->execute(array($clientName, $unitsNumber, $projectLink, $comment, $projectTask, $cad, $rkhsa, $sak, $cnr, $ecr, $floor, $html, $risk, $primarySort, $gis, $excel, $wd, $mt, $w, $completed, $member, $projectId));
                        $count = $stmt->rowCount();

                        if ($count > 0) {

                            echo "<div class='container'>";
                                echo "<div class='alert alert-success mt-5'>تم تعديل معاملة جديدة بنجاح</div>";
                                echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة تفاصيل المعاملة بعد 5 ثواني</div>";
                            header("refresh:5; url=tasks.php?do=detials&id=" . $projectId);
                            exit();
                            echo "</div>";

                        } else {

                            echo "<div class='container'>";
                                echo "<div class='alert alert-danger mt-5'>حدث خطأ حاول مرة أخرى</div>";
                            echo "</div>";
                        }

                    } else {

                        $stmt = $con->prepare("SELECT * FROM qma.projects WHERE id = ?");
                        $stmt->execute(array($projectIdSession));
                        $row = $stmt->fetch();

                        if ($clientName == $row['Client_Name'] && $member == $row['Member'] && $unitsNumber == $row['Units_Number']) {

                                $stmt = $con->prepare("UPDATE qma.projects SET 
                                                                Client_Name = ?,
                                                                Date = now(), 
                                                                Units_Number = ?, 
                                                                Project_Link = ?, 
                                                                Comments = ?, 
                                                                Project_Task = ?, 
                                                                CAD = ?, 
                                                                Rkhsa = ?, 
                                                                Sak = ?, 
                                                                CNR = ?, 
                                                                ECR = ?, 
                                                                Floor = ?, 
                                                                HTML = ?, 
                                                                Risk = ?, 
                                                                Primary_Sort = ?, 
                                                                GIS = ?, 
                                                                EXCEL = ?, 
                                                                WD = ?, 
                                                                MT = ?, 
                                                                W = ?, 
                                                                Member = ? 
                                                        WHERE 
                                                                id = ?");
                                $stmt->execute(array($clientName, $unitsNumber, $projectLink, $comment, $projectTask, $cad, $rkhsa, $sak, $cnr, $ecr, $floor, $html, $risk, $primarySort, $gis, $excel, $wd, $mt, $w, $member, $projectId));
                                $count = $stmt->rowCount();

                                if ($count > 0) {

                                echo "<div class='container'>";
                                    echo "<div class='alert alert-success mt-5'>تم تعديل معاملة جديدة بنجاح</div>";
                                    echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة تفاصيل المعاملة بعد 5 ثواني</div>";
                                header("refresh:5; url=tasks.php?do=detials&id=" . $projectId);
                                exit();
                                echo "</div>";

                                } else {

                                echo "<div class='container'>";
                                    echo "<div class='alert alert-danger mt-5'>حدث خطأ حاول مرة أخرى</div>";
                                echo "</div>";
                                }


                            } else {

                            echo "<div class='container'>";
                                echo "<div class='alert alert-danger mt-5'>غير مصرح لك بتغيير البيانات بهذه الطريقة</div>";
                                echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة تفاصيل المعاملة بعد 5 ثواني</div>";
                            header("refresh:5; url=tasks.php?do=detials&id=" . $projectId);
                            exit();
                            echo "</div>";     

                        }
                    }
                }

            }

        } elseif ($do == "delete") {

            $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE id = ? AND Group_ID = 1 OR id = ? AND Group_ID = 2");
            $stmt->execute(array($_SESSION['id'], $_SESSION['id']));
            $adminCheck = $stmt->rowCount();

            if ($adminCheck > 0) {

                $stmt = $con->prepare("SELECT * FROM qma.projects WHERE id = ?");
                $stmt->execute(array($_GET['id']));
                $count = $stmt->rowCount();
    
                if ($count > 0) {
    
                    $stmt = $con->prepare("DELETE FROM qma.projects WHERE id = ?");
                    $stmt->execute(array($_GET['id']));
                    $count = $stmt->rowCount();
    
    
                    echo "<div class='container mt-5 mb-3'>";
                        echo "<div class='alert alert-success'>تم حذف المعاملة بنجاح</div>";
                        echo "<div class='alert alert-info'>سيتم تحديث الصفحة بعد 5 ثواني</div>";
                        header("refresh:5; url=tasks.php");
                        echo "</div>";
                        exit();
                    
                } else {
    
                    echo "<div class='container'>";
                        echo "<div class='alert alert-warning mt-5'>المعاملة غير موجودة</div>";
                        echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة إدارة المعاملات بعد 5 ثوان</div>";
                        header("refresh:5;url=tasks.php");
                        exit();
                    echo "</div>";
    
                }

            } else {
                
                echo "<div class='container'>";
                    echo "<div class='alert alert-warning mt-5'>غير مصرح لك بالدخول إلى هذا الرابط</div>";
                    echo "<div class='alert alert-info'>سيتم الإنتقال إلى صفحة المعاملات بعد 5 ثوان</div>";
                    header("refresh:5;url=tasks.php");
                    exit();
                echo "</div>";

            }


        } elseif ($do == "insertcomment") {
            
            if ($_SERVER['REQUEST_METHOD'] == "POST") {

                $comment = filter_var($_POST['comment'], FILTER_SANITIZE_STRING);
                $projectId = filter_var($_POST['project_id'], FILTER_SANITIZE_NUMBER_INT);
                $userId = filter_var($_SESSION['id'], FILTER_SANITIZE_NUMBER_INT);

                $formErrors = [];

                if (empty($comment)) {
                    $formErrors[] = "لايمكن ترك حقل التعليق فارغا";
                }
                if (empty($projectId) || empty($userId)) {
                    $formErrors[] = "حدث خطأ حاول مرة أخرى";
                }

                if (empty($formErrors)) {

                    $stmt = $con->prepare("SELECT id FROM qma.projects WHERE id = ?");
                    $stmt->execute(array($projectId));
                    $count = $stmt->rowCount();
    
                    if ($count > 0) {
    
                        $stmt = $con->prepare("INSERT INTO
                                                    qma.comment
                                                    (Comment,
                                                    User_ID,
                                                    Project_ID)
                                                VALUES 
                                                    (?, ?, ?)");
                                                    
                        $stmt->execute(array($comment, $userId, $projectId));
                        $count = $stmt->rowCount();
    
                        if ($count > 0) {

                            echo "<div class='container'>";
                                echo "<div class='alert alert-success mt-5'>تم إضافة التعليق بنجاح</div>";
                                echo "<div class='alert alert-info'>سيتم تحديث الصفحة بعد 5 ثوان</div>";
                            header("refresh:5;url=tasks.php?do=detials&id=$projectId");
                            exit();
                            echo "</div>";
    
                            
                        } else {
    
                            echo "<div class='container'>";
                                echo "<div class='alert alert-danger mt-5'>حدث خطأ, حاول مرة أخرى</div>";
                                echo "<div class='alert alert-info'>سيتم تحديث الصفحة بعد 5 ثوان</div>";
                            header("refresh:5;url=tasks.php?do=detials&id=$projectId");
                            exit();
                            echo "</div>";
                            
                        }

                    } else {

                        echo "<div class='container'>";
                            echo "<div class='alert alert-danger mt-5'>هذه المعاملة غير موجودة</div>";
                            echo "<div class='alert alert-info'>سيتم تحديث الصفحة بعد 5 ثوان</div>";
                        header("refresh:5;url=tasks.php?do=detials&id=$projectId");
                        exit();
                        echo "</div>";

                    }

                } else {

                    echo "<div class='container'>";
                    foreach ($formErrors as $error) {
                        echo "<div class='alert alert-danger mt-5'>" . $error . "</div>";
                    }
                    echo "<div class='alert alert-info'>سيتم تحديث الصفحة بعد 5 ثوان</div>";
                    header("refresh:5;url=tasks.php?do=detials&id=$projectId");
                    exit();
                    echo "</div>";
                }

            } else {

                echo "<div class='container'>";
                    echo "<div class='alert alert-warning mt-5'>غير مصرح لك بدخول هذا الرابط</div>";
                    echo "<div class='alert alert-info'>سيتم الإنتقال إلى الصفحة الرئيسية بعد 5 ثوان</div>";
                    header("refresh:5;url=index.php");
                    exit();
                echo "</div>";
                
            }
        } elseif ($do == "editcomment") {

            $stmt = $con->prepare("SELECT * FROM qma.comment WHERE id = ?");
            $stmt->execute(array($_GET['id']));
            $count = $stmt->rowCount();
            $row = $stmt->fetch();

            if ($count > 0) {

                if ($row['User_ID'] == $_SESSION['id']) {


                    ?>
                    <div class="container">
                        <form action="tasks.php?do=updatecomment" method="POST">
                            <h2 class="text-center text-primary mt-5 mb-4">تعديل التعليق</h2>
                            <input type="hidden" name="user_id" value="<?php echo $_SESSION['id'] ?>">
                            <input type="hidden" name="comment_id" value="<?php echo $row['id'] ?>">
                            <input type="hidden" name="project_id" value="<?php echo $row['Project_ID'] ?>">
                            <div class="form-group mb-3">
                                <textarea class="form-control" name="comment" id="comment" rows="4" placeholder="أكتب ملاحظة أو تعليق"><?php echo $row['Comment'] ?></textarea>
                            </div>
                            <input class='btn btn-primary mb-4' type="submit" value="تحديث">
                        </form>
                    </div>

                <?php

                } else {

                    echo "<div class='container'>";
                        echo "<div class='alert alert-warning mt-5'>لايمكن تعديل التعليق بهذه الطريقة </div>";
                        echo "<div class='alert alert-info'>سيتم تحديث إلى الصفحة الرئيسية بعد 5 ثوان</div>";
                    header("refresh:5;url=index.php");
                    exit();
                    echo "</div>";
                    

                }

            } else {

                echo "<div class='container'>";
                    echo "<div class='alert alert-warning mt-5'>هذا التعليق غير موجود</div>";
                    echo "<div class='alert alert-info'>سيتم تحديث إلى الصفحة الرئيسية بعد 5 ثوان</div>";
                    header("refresh:5;url=index.php");
                    exit();
                echo "</div>";

            }
        } elseif ($do == "updatecomment") {

            if ($_SERVER['REQUEST_METHOD'] == "POST") {

                $projectIdSession = $_SESSION['project_id'];

                $projectId = filter_var($_POST['project_id'], FILTER_SANITIZE_NUMBER_INT);
                $userId = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
                $comment = filter_var($_POST['comment'], FILTER_SANITIZE_STRING);
                $commentId = filter_var($_POST['comment_id'], FILTER_SANITIZE_NUMBER_INT);

                $stmt = $con->prepare("SELECT 
                                            * 
                                        FROM 
                                            qma.comment 
                                        WHERE 
                                            id = ? 
                                        AND 
                                            Project_ID = ? 
                                        AND 
                                            User_ID = ?");
                $stmt->execute(array($commentId, $projectIdSession, $userId));
                $count = $stmt->rowCount();

                if ($count > 0) {

                    $formErrors = [];

                    if (empty($comment)) {
                        $formErrors[] = "لايمكن ترك حقل التعليق فارغا";
                    }
                    if (empty($projectId) || empty($userId)) {
                        $formErrors[] = "حدث خطأ حاول مرة أخرى";
                    }
    
                    if (empty($formErrors)) {
    
                        $stmt = $con->prepare("UPDATE
                                                    qma.comment
                                                SET
                                                    Comment = ?,
                                                    User_ID = ?,
                                                    Project_ID = ?,
                                                    Date = now()
                                                WHERE
                                                    id = ?");
                        $stmt->execute(array($comment, $userId, $projectId, $commentId));
                        $count = $stmt->rowCount();
    
                        if ($count > 0) {
    
                            echo "<div class='container mt-5 mb-3'>";
                            echo "<div class='alert alert-success'>تم تعديل التعليق بنجاح</div>";
                            echo "<div class='alert alert-info'>سيتم تحديث الصفحة بعد 5 ثواني</div>";
                            header("refresh:5; url=tasks.php?do=detials&id=$projectId");
                            echo "</div>";
                            exit();
    
                        }
    
                    } else {
                        
                        echo "<div class='container mt-5 mb-3'>";
                        foreach ($formErrors as $error) {
    
                            echo "<div class='alert alert-danger'>" . $error . "</div>";
    
                        }
                        echo "<div class='alert alert-info'>سيتم تحديث الصفحة بعد 5 ثواني</div>";
                        header("refresh:5; url=tasks.php?do=detials&id=$projectId");
                        echo "</div>";
                        exit();
    
                    }

                } else {
                    echo "<div class='container mt-5 mb-3'>";
                    echo "<div class='alert alert-warning'>حدث خطأ, يرجى المحاولة مرة أخرى</div>";
                    echo "<div class='alert alert-info'>سيتم الإنتقال إلى الصفحة الرئيسية بعد 5 ثواني</div>";
                    header("refresh:5; url=index.php");
                    echo "</div>";
                    exit();
                }


            }

        } elseif ($do == "deletecomment") {

            $stmt = $con->prepare("SELECT users.id, users.Group_ID FROM qma.users WHERE id = ? AND Group_ID = 1 OR id = ? AND Group_ID = 2");
            $stmt->execute(array($_SESSION['id'], $_SESSION['id']));
            $adminCheck = $stmt->rowCount();

            $stmt = $con->prepare("SELECT id, User_ID FROM qma.comment WHERE id = ?");
            $stmt->execute(array($_GET['id']));
            $row = $stmt->fetch();
            $id = $row['id'];

            if ($adminCheck > 0 || $row['User_ID'] == $_SESSION['id'] ) {

                $stmt = $con->prepare("SELECT * FROM qma.comment WHERE id = ?");
                $stmt->execute(array($_GET['id']));
                $projectId = $stmt->fetch();
                $projectId = $projectId['Project_ID'];
                $count = $stmt->rowCount();
    
                if ($count > 0) {
    
                    $stmt = $con->prepare("DELETE FROM qma.comment WHERE id = ?");
                    $stmt->execute(array($_GET['id']));
                    $count = $stmt->rowCount();

                    if ($count > 0) {
                        echo "<div class='container mt-5 mb-3'>";
                            echo "<div class='alert alert-success'>تم حذف التعليق بنجاح</div>";
                            echo "<div class='alert alert-info'>سيتم تحديث الصفحة بعد 5 ثواني</div>";
                            header("refresh:5; url=tasks.php?do=detials&id=$projectId");
                            echo "</div>";
                        exit();                    
                    
                    } else {

                        echo "<div class='container'>";
                            echo "<div class='alert alert-warning mt-5'>حدث خطأ, حاول مرة أخرى</div>";
                            echo "<div class='alert alert-info'>سيتم الإنتقال إلى الإنتقال إلى الصفحة الرئيسية بعد 5 ثوان</div>";
                            header("refresh:5;url=index.php");
                            exit();
                        echo "</div>";
                    
                    }
                    
                } else {
    
                    echo "<div class='container'>";
                        echo "<div class='alert alert-warning mt-5'>التعليق غير موجود</div>";
                        echo "<div class='alert alert-info'>سيتم الإنتقال إلى الإنتقال إلى الصفحة الرئيسية بعد 5 ثوان</div>";
                        header("refresh:5;url=tasks.php?do=detials&id=$projectId");
                        exit();
                    echo "</div>";
                }
        }
    }
        
    } else {

        header("Location: login.php");
        exit();

    }
        include "templates/footer.php";
    ?>