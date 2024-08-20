<?php include 'db_connect.php'; ?>
<div class="col-lg-12">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <div class="card-tools">
                <a class="btn btn-block btn-sm btn-default btn-flat border-primary new_staff" href="javascript:void(0)"><i class="fa fa-plus"></i> Add New</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-hover table-bordered" id="list">
                <colgroup>
                    <col width="5%">
                    <col width="35%">
                    <col width="20%">
                    <col width="20%">
                    <col width="20%">
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Staff Name</th>
                        <th>Questions</th>
                        <th>Answered</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $qry = $conn->query("SELECT * FROM staff_list ORDER BY lastname ASC, firstname ASC");
                    while ($row = $qry->fetch_assoc()):
                        // Check if the staff_id column exists in the question_list and evaluation_list tables
                        $questions = $conn->query("SELECT * FROM question_list WHERE staff_id = {$row['id']}")->num_rows;
                        $answers = $conn->query("SELECT * FROM evaluation_list WHERE staff_id = {$row['id']}")->num_rows;
                    ?>
                    <tr>
                        <th class="text-center"><?php echo $i++ ?></th>
                        <td><b><?php echo $row['lastname'] . ', ' . $row['firstname'] ?></b></td>
                        <td class="text-center"><b><?php echo number_format($questions) ?></b></td>
                        <td class="text-center"><b><?php echo number_format($answers) ?></b></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-default btn-sm btn-flat border-info wave-effect text-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                Action
                            </button>
                            <div class="dropdown-menu" style="">
                                <a class="dropdown-item manage_questionnaire" href="index.php?page=manage_staff_questionnaire&id=<?php echo $row['id'] ?>">Manage</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $('.new_staff').click(function(){
            uni_modal("New Staff", "<?php echo $_SESSION['login_view_folder'] ?>manage_staff.php")
        })
        $('.manage_questionnaire').click(function(){
            uni_modal("Manage Questionnaire", "<?php echo $_SESSION['login_view_folder'] ?>manage_staff_questionnaire.php?id=" + $(this).attr('data-id'))
        })
        $('.delete_staff').click(function(){
            _conf("Are you sure to delete this staff?", "delete_staff", [$(this).attr('data-id')])
        })
        $('#list').dataTable()
    })
    function delete_staff($id){
        start_load()
        $.ajax({
            url:'ajax.php?action=delete_staff',
            method:'POST',
            data:{id:$id},
            success:function(resp){
                if(resp==1){
                    alert_toast("Data successfully deleted", 'success')
                    setTimeout(function(){
                        location.reload()
                    },1500)
                }
            }
        })
    }
</script>
