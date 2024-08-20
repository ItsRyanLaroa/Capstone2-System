<?php 
include 'db_connect.php';
if(isset($_GET['id'])){
	$qry = $conn->query("SELECT * FROM staff_list WHERE id = ".$_GET['id'])->fetch_array();
	foreach($qry as $k => $v){
		$$k = $v;
	}
}
?>
<div class="container-fluid">
	<form action="" id="manage-staff-restriction">
		<div class="row">
			<div class="col-md-4 border-right">
				<input type="hidden" name="staff_id" value="<?php echo isset($id) ? $id : '' ?>">
				<div id="msg" class="form-group"></div>
				<div class="form-group">
					<label for="" class="control-label">Department</label>
					<select name="department_id" id="department_id" class="form-control form-control-sm select2">
						<option value=""></option>
						<?php 
						$departments = $conn->query("SELECT id, name FROM department_list");
						$departments_arr = [];
						while($row=$departments->fetch_assoc()):
							$departments_arr[$row['id']] = $row;
						?>
						<option value="<?php echo $row['id'] ?>" <?php echo isset($department_id) && $department_id == $row['id'] ? 'selected' : '' ?>><?php echo $row['name'] ?></option>
						<?php endwhile; ?>
					</select>
				</div>
				<div class="form-group">
					<label for="" class="control-label">Position</label>
					<select name="position_id" id="position_id" class="form-control form-control-sm select2">
						<option value=""></option>
						<?php 
						$positions = $conn->query("SELECT id, name FROM position_list");
						$positions_arr = [];
						while($row=$positions->fetch_assoc()):
							$positions_arr[$row['id']] = $row;
						?>
						<option value="<?php echo $row['id'] ?>" <?php echo isset($position_id) && $position_id == $row['id'] ? 'selected' : '' ?>><?php echo $row['name'] ?></option>
						<?php endwhile; ?>
					</select>
				</div>
				<div class="form-group">
					<div class="d-flex w-100 justify-content-center">
						<button class="btn btn-sm btn-flat btn-primary bg-gradient-primary" id="add_to_list" type="button">Add to List</button>
					</div>
				</div>
			</div>
			<div class="col-md-8">
				<table class="table table-condensed" id="r-list">
					<thead>
						<tr>
							<th>Department</th>
							<th>Position</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$restriction = $conn->query("SELECT * FROM staff_restriction_list WHERE staff_id = {$_GET['id']} ORDER BY id ASC");
						while($row=$restriction->fetch_assoc()):
						?>
						<tr>
							<td>
								<b><?php 
								$department = $conn->query("SELECT name FROM department_list WHERE id = ".$row['department_id'])->fetch_assoc();
								echo isset($department['name']) ? $department['name'] : '' 
								?></b>
								<input type="hidden" name="rid[]" value="<?php echo $row['id'] ?>">
								<input type="hidden" name="department_id[]" value="<?php echo $row['department_id'] ?>">
							</td>
							<td>
								<b><?php 
								$position = $conn->query("SELECT name FROM position_list WHERE id = ".$row['position_id'])->fetch_assoc();
								echo isset($position['name']) ? $position['name'] : '' 
								?></b>
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
					}else if(resp == 2){
						$('#msg').html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Restriction already exists.</div>');
						end_load();
					}
				}
			});
		});
		$('#add_to_list').click(function(){
			start_load();
			var frm = $('#manage-staff-restriction');
			var dep_id = frm.find('#department_id').val();
			var pos_id = frm.find('#position_id').val();
			var dep_arr = <?php echo json_encode($departments_arr) ?>;
			var pos_arr = <?php echo json_encode($positions_arr) ?>;
			var tr = $("<tr></tr>");
			tr.append('<td><b>'+dep_arr[dep_id].name+'</b><input type="hidden" name="rid[]" value=""><input type="hidden" name="department_id[]" value="'+dep_id+'"></td>');
			tr.append('<td><b>'+pos_arr[pos_id].name+'</b><input type="hidden" name="position_id[]" value="'+pos_id+'"></td>');
			tr.append('<td class="text-center"><span class="btn btn-sm btn-outline-danger" onclick="$(this).closest(\'tr\').remove()" type="button"><i class="fa fa-trash"></i></span></td>');
			$('#r-list tbody').append(tr);
			frm.find('#department_id').val('').trigger('change');
			frm.find('#position_id').val('').trigger('change');
			end_load();
		});
	});
</script>
