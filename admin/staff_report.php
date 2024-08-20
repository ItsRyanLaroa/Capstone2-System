<?php $staff_id = isset($_GET['sid']) ? $_GET['sid'] : '' ; ?>
<?php 
function ordinal_suffix($num){
    $num = $num % 100;
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
<div class="col-lg-12">
    <div class="callout callout-info">
        <div class="d-flex w-100 justify-content-center align-items-center">
            <label for="staff">Select Staff</label>
            <div class="mx-2 col-md-4">
            <select name="" id="staff_id" class="form-control form-control-sm select2">
                <option value=""></option>
                <?php 
                $staff = $conn->query("SELECT *,concat(firstname,' ',lastname) as name FROM staff_list order by concat(firstname,' ',lastname) asc");
                $s_arr = array();
                $sname = array();
                while($row=$staff->fetch_assoc()):
                    $s_arr[$row['id']]= $row;
                    $sname[$row['id']]= ucwords($row['name']);
                ?>
                <option value="<?php echo $row['id'] ?>" <?php echo isset($staff_id) && $staff_id == $row['id'] ? "selected" : "" ?>><?php echo ucwords($row['name']) ?></option>
                <?php endwhile; ?>
            </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 mb-1">
            <div class="d-flex justify-content-end w-100">
                <button class="btn btn-sm btn-success bg-gradient-success" style="display:none" id="print-btn"><i class="fa fa-print"></i> Print</button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="callout callout-info">
                <div class="list-group" id="position-list">
                    
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="callout callout-info" id="printable">
                <div>
                    <h3 class="text-center">Evaluation Report</h3>
                    <hr>
                    <table width="100%">
                        <tr>
                            <td width="50%"><p><b>Staff: <span id="sname"></span></b></p></td>
                            <td width="50%"><p><b>Academic Year: <span id="ay"><?php echo $_SESSION['academic']['year'].' '.(ordinal_suffix($_SESSION['academic']['semester'])) ?> Semester</span></b></p></td>
                        </tr>
                        <tr>
                            <td width="50%"><p><b>Position: <span id="positionField"></span></b></p></td>
                        </tr>
                    </table>
                    <p class=""><b>Total Evaluations: <span id="tse"></span></b></p>
                </div>
                <fieldset class="border border-info p-2 w-100">
                   <legend  class="w-auto">Rating Legend</legend>
                   <p>5 = Strongly Agree, 4 = Agree, 3 = Uncertain, 2 = Disagree, 1 = Strongly Disagree</p>
                </fieldset>
                <?php 
                    $q_arr = array();
                    $criteria = $conn->query("SELECT * FROM criteria_list where id in (SELECT criteria_id FROM question_list where academic_id = {$_SESSION['academic']['id']} ) order by abs(order_by) asc ");
                    while($crow = $criteria->fetch_assoc()):
                ?>
                <table class="table table-condensed wborder">
                    <thead>
                        <tr class="bg-gradient-secondary">
                            <th class=" p-1"><b><?php echo $crow['criteria'] ?></b></th>
                            <th width="5%" class="text-center">1</th>
                            <th width="5%" class="text-center">2</th>
                            <th width="5%" class="text-center">3</th>
                            <th width="5%" class="text-center">4</th>
                            <th width="5%" class="text-center">5</th>
                        </tr>
                    </thead>
                    <tbody class="tr-sortable">
                        <?php 
                        $questions = $conn->query("SELECT * FROM question_list where criteria_id = {$crow['id']} and academic_id = {$_SESSION['academic']['id']} order by abs(order_by) asc ");
                        while($row=$questions->fetch_assoc()):
                        $q_arr[$row['id']] = $row;
                        ?>
                        <tr class="bg-white">
                            <td class="p-1" width="40%">
                                <?php echo $row['question'] ?>
                            </td>
                            <?php for($c=1;$c<=5;$c++): ?>
                            <td class="text-center">
                                <span class="rate_<?php echo $c.'_'.$row['id'] ?> rates"></span>
                            </td>
                            <?php endfor; ?>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .list-group-item:hover{
        color: black !important;
        font-weight: 700 !important;
    }
</style>

<noscript>
    <style>
        table{
            width:100%;
            border-collapse: collapse;
        }
        table.wborder tr,table.wborder td,table.wborder th{
            border:1px solid gray;
            padding: 3px
        }
        table.wborder thead tr{
            background: #6c757d linear-gradient(180deg,#828a91,#6c757d) repeat-x!important;
            color: #fff;
        }
        .text-center{
            text-align:center;
        } 
        .text-right{
            text-align:right;
        } 
        .text-left{
            text-align:left;
        } 
    </style>
</noscript>

<script>
    $(document).ready(function(){
        $('#staff_id').change(function(){
            if($(this).val() > 0)
            window.history.pushState({}, null, './staff_report.php?sid='+$(this).val());
            load_position()
        })
        if($('#staff_id').val() > 0)
            load_position()
    })
    function load_position(){
        start_load()
        var sname = <?php echo json_encode($sname) ?>;
        $('#sname').text(sname[$('#staff_id').val()])
        $.ajax({
            url:"ajax.php?action=get_position",
            method:'POST',
            data:{sid:$('#staff_id').val()},
            error:function(err){
                console.log(err)
                alert_toast("An error occurred",'error')
                end_load()
            },
            success:function(resp){
                if(resp){
                    resp = JSON.parse(resp)
                    if(Object.keys(resp).length <= 0 ){
                        $('#position-list').html('<a href="javascript:void(0)" class="list-group-item list-group-item-action disabled">No data to display.</a>')
                    }else{
                        $('#position-list').html('')
                        Object.keys(resp).map(k=>{
                        $('#position-list').append('<a href="javascript:void(0)" data-json=\''+JSON.stringify(resp[k])+'\' data-id="'+resp[k].id+'" class="list-group-item list-group-item-action show-result">'+resp[k].position+'</a>')
                        })

                    }
                }
            },
            complete:function(){
                end_load()
                anchor_func()
                if('<?php echo isset($_GET['rid']) ?>' == 1){
                    $('.show-result[data-id="<?php echo isset($_GET['rid']) ? $_GET['rid'] : '' ?>"]').trigger('click')
                }else{
                    $('.show-result').first().trigger('click')
                }
            }
        })
    }
    function anchor_func(){
        $('.show-result').click(function(){
            var vars = [], hash;
            var data = $(this).attr('data-json')
                data = JSON.parse(data)
            var _href = location.href.slice(window.location.href.indexOf('?') + 1).split('&');
            for(var i = 0; i < _href.length; i++)
                {
                    hash = _href[i].split('=');
                    vars[hash[0]] = hash[1];
                }
            window.history.pushState({}, null, './staff_report.php?sid='+vars.sid+'&rid='+data.id);
            load_report(vars.sid,data.position_id,data.id);
            $('#positionField').text(data.position)
            $('.show-result.active').removeClass('active')
            $(this).addClass('active')
        })
    }
    function load_report($staff_id, $position_id, $position_class_id){
        if($('#preloader2').length <= 0)
        start_load()
        $.ajax({
            url:'ajax.php?action=get_staff_report',
            method:"POST"
            data:{staff_id: $staff_id, position_id: $position_id, position_class_id: $position_class_id},
            error:function(err){
                console.log(err)
                alert_toast("An error occurred.",'error')
                end_load()
            },
            success:function(resp){
                if(resp){
                    resp = JSON.parse(resp)
                    Object.keys(resp.ratings).map(qid=>{
                        for(var i = 1; i <= 5; i++){
                            $('.rate_'+i+'_'+qid).text('')
                        }
                        resp.ratings[qid].map(rate=>{
                            $('.rate_'+rate.rating+'_'+qid).text(rate.count)
                        })
                    })
                    $('#tse').text(resp.total)
                    $('#print-btn').show()
                } else {
                    alert_toast("No evaluation data available.",'info')
                    $('#tse').text('0')
                    $('.rates').text('')
                    $('#print-btn').hide()
                }
            },
            complete:function(){
                end_load()
            }
        })
    }
    $('#print-btn').click(function(){
        start_load()
        var _h = $('head').clone()
        var _p = $('#printable').clone()

        var ns = $('noscript').clone().html()
        _p.prepend(ns)
        _p.prepend(_h)
        var nw = window.open("","_blank","width=900,height=600")
        nw.document.write(_p.html())
        nw.document.close()
        nw.print()
        setTimeout(function(){
            nw.close()
            end_load()
        },750)
    })
</script>
