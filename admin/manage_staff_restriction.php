<?php
include '../db_connect.php';
?>
<div class="container-fluid">
    <form action="" id="manage-staff-restriction">
        <div class="row">
            <div class="col-md-4 border-right">
                <input type="hidden" name="staff_id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '' ?>">
                <div id="msg" class="form-group"></div>
                <div class="form-group">
                    <label for="staff_id" class="control-label">Staff</label>
                    <select name="staff_id" id="staff_id" class="form-control form-control-sm select2" required>
                        <option value=""></option>
                        <?php 
                        $staff = $conn->query("SELECT *, CONCAT(firstname, ' ', lastname) AS name FROM staff_list ORDER BY CONCAT(firstname, ' ', lastname) ASC");
                        while($row = $staff->fetch_assoc()):
                        ?>
                        <option value="<?php echo $row['id'] ?>"><?php echo ucwords($row['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="class_id" class="control-label">Class</label>
                    <select name="class_id" id="class_id" class="form-control form-control-sm select2" required>
                        <option value=""></option>
                        <?php 
                        $classes = $conn->query("SELECT id, CONCAT(curriculum, ' ', level, ' - ', section) AS class FROM class_list");
                        while($row = $classes->fetch_assoc()):
                        ?>
                        <option value="<?php echo $row['id'] ?>"><?php echo $row['class'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
       
            </div>
            <div class="col-md-8">
                <table class="table table-condensed" id="r-list">
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $restriction = $conn->query("SELECT * FROM staff_restriction_list WHERE staff_id = {$_GET['id']} ORDER BY id ASC");
                        while($row = $restriction->fetch_assoc()):
                        ?>
                        <tr>
                            <td>
                                <b><?php echo isset($s_arr[$row['staff_id']]) ? $s_arr[$row['staff_id']]['name'] : '' ?></b>
                                <input type="hidden" name="rid[]" value="<?php echo $row['id'] ?>">
                                <input type="hidden" name="staff_id[]" value="<?php echo $row['staff_id'] ?>">
                            </td>
                            <td>
                                <b><?php echo isset($d_arr[$row['department_id']]) ? $d_arr[$row['department_id']]['name'] : '' ?></b>
                                <input type="hidden" name="department_id[]" value="<?php echo $row['department_id'] ?>">
                            </td>
                            <td>
                                <b><?php echo isset($p_arr[$row['position_id']]) ? $p_arr[$row['position_id']]['name'] : '' ?></b>
                                <input type="hidden" name="position_id[]" value="<?php echo $row['position_id'] ?>">
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-danger" onclick="$(this).closest('tr').remove()" type="button"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>
<script>
  $(document).ready(function(){
    $('.select2').select2({
        placeholder: "Please select here",
        width: "100%"
    });

    $('#manage-staff-restriction').submit(function(e){
        e.preventDefault();
        start_load();
        $('#msg').html('');
        $.ajax({
            url: 'ajax.php?action=save_staff_restriction',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp){
                if(resp == 1){
                    alert_toast("Data successfully saved.", "success");
                    setTimeout(function(){
                        location.reload();    
                    }, 1750);
                } else if(resp == 2){
                    $('#msg').html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Error saving data.</div>');
                    end_load();
                }
            }
        });
    });
});

</script>
