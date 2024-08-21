<?php 
include 'db_connect.php';

if(isset($_GET['id'])){
    $staff_id = intval($_GET['id']);
    $qry = $conn->query("SELECT * FROM staff_list WHERE id = $staff_id");
    $staff = $qry->fetch_assoc();
    
    if ($staff) {
        $staff_name = $staff['firstname'];
        foreach($staff as $k => $v){
            $$k = $v;
        }
    } else {
        $staff_name = 'Unknown'; // Default value if staff not found
    }
}

function ordinal_suffix($num){
    $num = $num % 100; // protect against large numbers
    if($num < 11 || $num > 13){
         switch($num % 10){
            case 1: return $num.'st';
            case 2: return $num.'nd';
            case 3: return $num.'rd';
        }
    }
    return $num.'th';
}
?>
<style>
    .card-info:not(.card-outline)>.card-header {
        background-color: #b31b1b;
    }
    .bg-gradient-primary {
        background: #007BFF linear-gradient(180deg, #B31B1A, #b31b1b) repeat-x !important;
        color: #fff;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card card-info card-primary">
                <div class="card-header">
                    <b>Question Form</b>
                </div>
                <div class="card-body">
                    <form action="" id="manage-question">
                        <input type="hidden" name="staff_id" value="<?php echo isset($staff_id) ? $staff_id : '' ?>">
                        <input type="hidden" name="id" value="">
                        <input type="hidden" name="student_id" value="<!-- Pass student ID here -->">
                        <div class="form-group">
                            <label for="">Criteria</label>
                            <select name="criteria_id" id="criteria_id" class="custom-select custom-select-sm select2">
                                <option value=""></option>
                            <?php 
                                $criteria = $conn->query("SELECT * FROM criteria_list ORDER BY abs(order_by) ASC");
                                while($row = $criteria->fetch_assoc()):
                            ?>
                            <option value="<?php echo $row['id'] ?>"><?php echo $row['criteria'] ?></option>
                            <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Question</label>
                            <textarea name="question" id="question" cols="30" rows="4" class="form-control" required=""><?php echo isset($question) ? $question : '' ?></textarea>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end w-100">
                        <button class="btn btn-sm btn-primary btn-flat bg-gradient-primary mx-1" form="manage-question">Save</button>
                        <button class="btn btn-sm btn-flat btn-secondary bg-gradient-secondary mx-1" form="manage-question" type="reset">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <b>Evaluation Questionnaire for Staff: <?php echo htmlspecialchars($staff_name) ?> </b>
                    <div class="card-tools">
                        <button class="btn btn-sm btn-flat btn-success bg-gradient-success mx-1" form="order-question">Save Order</button>
                    </div>
                </div>
                <div class="card-body">
                    <fieldset class="border border-info p-2 w-100">
                       <legend class="w-auto">Rating Legend</legend>
                       <p>5 = Strongly Agree, 4 = Agree, 3 = Uncertain, 2 = Disagree, 1 = Strongly Disagree</p>
                    </fieldset>
                    <form id="order-question">
                    <div class="clear-fix mt-2"></div>
                    <?php 
                        $q_arr = array();
                        $criteria = $conn->query("SELECT * FROM criteria_list ORDER BY abs(order_by) ASC");
                        while($crow = $criteria->fetch_assoc()):
                    ?>
                    <table class="table table-condensed">
                        <thead>
                            <tr class="bg-gradient-secondary">
                                <th colspan="2" class="p-1"><b><?php echo $crow['criteria'] ?></b></th>
                                <th class="text-center">5</th>
                                <th class="text-center">4</th>
                                <th class="text-center">3</th>
                                <th class="text-center">2</th>
                                <th class="text-center">1</th>
                            </tr>
                        </thead>
                        <tbody class="tr-sortable">
                            <?php 
                            $questions = $conn->query("SELECT * FROM question_list WHERE criteria_id = {$crow['id']} AND staff_id = $staff_id ORDER BY abs(order_by) ASC");
                            while($row = $questions->fetch_assoc()):
                            $q_arr[$row['id']] = $row;
                            ?>
                            <tr class="bg-white">
                                <td class="p-1 text-center" width="5px">
                                    <span class="btn-group dropright">
                                      <span type="button" class="btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                       <i class="fa fa-ellipsis-v"></i>
                                      </span>
                                      <div class="dropdown-menu">
                                         <a class="dropdown-item edit_question" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">Edit</a>
                                          <div class="dropdown-divider"></div>
                                         <a class="dropdown-item delete_question" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">Delete</a>
                                      </div>
                                    </span>
                                </td>
                                <td class="p-1" width="40%">
                                    <?php echo $row['question'] ?>
                                    <input type="hidden" name="qid[]" value="<?php echo $row['id'] ?>">
                                </td>
                                <?php for($c = 0; $c < 5; $c++): ?>
                                <td class="text-center">
                                    <div class="icheck-success d-inline">
                                        <input type="radio" name="qid[<?php echo $row['id'] ?>][]" id="qradio<?php echo $row['id'].'_'.$c ?>" value="<?php echo $c + 1 ?>">
                                        <label for="qradio<?php echo $row['id'].'_'.$c ?>"></label>
                                  </div>
                                </td>
                                <?php endfor; ?>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php endwhile; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('.select2').select2({
            placeholder: "Please select here",
            width: "100%"
        });
    });

    $('.edit_question').click(function(){
        var id = $(this).attr('data-id');
        var question = <?php echo json_encode($q_arr) ?>;
        $('#manage-question').find("[name='id']").val(question[id].id);
        $('#manage-question').find("[name='question']").val(question[id].question);
        $('#manage-question').find("[name='criteria_id']").val(question[id].criteria_id).trigger('change');
    });

    $('.delete_question').click(function(){
        _conf("Are you sure to delete this question?", "delete_question", [$(this).attr('data-id')]);
    });

    $('.tr-sortable').sortable();

    $('#manage-question').on('reset', function(){
        $(this).find('input[name="id"]').val('');
        $('#manage-question').find("[name='criteria_id']").val('').trigger('change');
        $('#manage-question').find("[name='question']").val('');
    });

    function delete_question(id){
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_question',
            method: 'POST',
            data: {id: id},
            success: function(resp){
                if(resp == 1){
                    alert_toast('Question successfully deleted.', 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                } else {
                    alert_toast('An error occurred. Please try again.', 'error');
                }
                end_load();
            }
        });
    }
	$('#manage-question').submit(function(e){
    e.preventDefault();
    start_load();

    var formData = new FormData($(this)[0]);
    formData.append('student_id', $('#student_id').val()); // Add student ID to FormData

    $.ajax({
        url: 'ajax.php?action=save_staff_evaluation',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        method: 'POST',
        success: function(resp){
            if(resp == 1){
                alert_toast('Data successfully saved.', 'success');
                setTimeout(function(){
                    location.reload();
                }, 1500);
            } else {
                alert_toast('An error occurred. Please try again.', 'error');
            }
            end_load();
        }
    });
});

</script>
