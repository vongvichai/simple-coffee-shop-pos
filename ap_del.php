<?php
date_default_timezone_set('Asia/Bangkok');
include_once("connectdb.php");
include_once("myfunctions.php"); 
$link = connect_db();
select_db();
$success=true;
if (isset($_POST['comid'])) {
	
	
	$comid = $_POST['comid'];
	$doctype = $_POST['doctype'];
	$docno = $_POST['docno'];
	$creator = $_POST['creator'];
    $datecreate = date('YmdHis');
	
	
	
    mysql_query("BEGIN");	 
	
	$sql = "update ic set isdelete='1', isenable=0,
						  deleteby='$creator',
						  datedelete='$datecreate'
			where comid='$comid' and doctype='$doctype' and docno='$docno' ";
			if (!mysql_query($sql,$link)) {
				$ret = array('result'=>0, 'msg'=>$sql );
				$success=false;
			}
			
	if ($success) {
		
		$sql = "select ref_doctype, ref_docno, ref_linenbr, qty from ic_det 
				where comid='$comid' and doctype='$doctype' and docno='$docno' and ref_doctype <> '' ";
				
		$res=mysql_query($sql,$link);
		while ($rs=mysql_fetch_array($res)) {
			
			$ref_doctype = $rs['ref_doctype'];
			$ref_docno = $rs['ref_docno'];
			$ref_linenbr = $rs['ref_linenbr'];	
			$qty = $rs['qty'];
			
			if ($ref_doctype=='PO') {

				$sql =  "update po_det set qty_clear = qty_clear -   $qty  
						where comid='$comid' and doctype='$ref_doctype' and docno='$ref_docno' and linenbr=$ref_linenbr ";
			}

			if ($ref_doctype=='SO') {

				$sql =  "update so_det set qty_clear = qty_clear -   $qty  
						where comid='$comid' and doctype='$ref_doctype' and docno='$ref_docno' and linenbr=$ref_linenbr ";
			}
			
			if (!mysql_query($sql,$link)) {
				$ret = array('result'=>0, 'msg'=>$sql );
				$success=false;
				break;
			}
		}			
		
		
		/*if ($success) {
		
			$sql = "delete from ic_det where comid='$comid' and doctype='$doctype' and docno='$docno' ";
			if (!mysql_query($sql,$link)) {
				$ret = array('result'=>0, 'msg'=>$sql );
				$success=false;
			}	
		}
		*/
		
	}	
	
	/*if ($success) {
		$sql = "delete from ic_pay where comid='$comid' and doctype='$doctype' and docno='$docno' ";
		if (!mysql_query($sql,$link)) {
			$ret = array('result'=>0, 'msg'=>$sql );
			$success=false;
		}			
	}	
	if ($success) {
		$sql = "delete from ic_rev where comid='$comid' and doctype='$doctype' and docno='$docno' ";
		if (!mysql_query($sql,$link)) {
			$ret = array('result'=>0, 'msg'=>$sql );
			$success=false;
		}			
	}
	*/

		

	

	//////////////////////////////////////////////////////
	end_process:
	
	
	if ($success) {
		$ret = array('result'=>1, 'msg'=>$docno );
		mysql_query('COMMIT');
	} else {
        mysql_query('ROLLBACK');
    }		

	$key = json_encode($ret);	
	echo $key;


}




?>


