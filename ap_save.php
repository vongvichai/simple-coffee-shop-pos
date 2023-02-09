<?php
date_default_timezone_set('Asia/Bangkok');
include_once("connectdb.php");
include_once("myfunctions.php"); 
$link = connect_db();
select_db();
$success=true;
if (isset($_POST['header'])) {
	$header = json_decode($_POST["header"],true);
	
	if ( isset($_POST["items"] )) {
		$items = json_decode( $_POST["items"],true);
	}
	if ( isset($_POST["payments"] )) {
		$payments = json_decode( $_POST["payments"],true);
	}
	if ( isset($_POST["revexps"] )) {
		$revexps = json_decode( $_POST["revexps"],true);
	}
	
	$arr = $header[0];		
	$mode = $arr['mode'];
	$comid = $arr['comid'];
	$doctype = $arr['doctype'];
	$refcode = strtoupper($arr['refcode']);
	$docno = $arr['docno'];
	$docdate = $arr['docdate'];
	$whcode = strtoupper($arr['whcode']);
	
	$custcode = strtoupper($arr['custcode']);
	$dptcode = strtoupper($arr['dptcode']);
	$projcode = strtoupper($arr['projcode']);
	
	$emp_req = strtoupper($arr['emp_req']);
	$emp_approve = strtoupper($arr['emp_approve']);
	
	
	$remark =  mysql_real_escape_string($arr['remark']);
	$remark2= mysql_real_escape_string($arr['remark2']);
	$remark3 = mysql_real_escape_string($arr['remark3']);
	$remark4 = mysql_real_escape_string($arr['remark4']);
	$remark5 = mysql_real_escape_string($arr['remark5']);
	$remark6 = mysql_real_escape_string($arr['remark6']);
	
	
	$vattype = $arr['vattype'];
	$totamt_calvat = $arr['totamt_calvat'];
	$totamt = $arr['totamt'];
	$discamt = $arr['discamt'];
	
	$gold_sal_b=$arr['gold_sal_b'];
	$gold_ret_b=$arr['gold_ret_b'];
	$gold_ret_g=$arr['gold_ret_g'];
	
	$totamt_beforevat=$arr['totamt_beforevat'];
	$gold_ret_amt=$arr['gold_ret_amt'];
	$gold_vat_diff=$arr['gold_vat_diff'];	
	
	
	
    $vatamt = $arr['vatamt'];
    $totamt_incvat = $arr['totamt_incvat'];
	$wtaxamt = $arr['wtaxamt'];
	$netamt = $arr['netamt'];
	
	$sale_to = $arr['sale_to'];
	$creditdays = $arr['creditdays'];
	$paytypecode = ''; //$arr['paytypecode'];
	//====================
	$invno=$arr['invno'];
	$invdate=$arr['invdate'];
	$duedate=$arr['duedate'];
	$docref=$arr['docref'];
	
	$sum_payamt=$arr['sum_payamt'];
	$bal_payamt=$arr['bal_payamt'];
	
	$sum_addamt=$arr['sum_addamt'];
	$sum_decamt=$arr['sum_decamt'];
	

	
	$wtaxno=$arr['wtaxno'];
	$wtaxdate=$arr['wtaxdate'];
	$wtaxmonth=$arr['wtaxmonth'];
	
	$wtaxline=$arr['wtaxline'];
	$wtaxnote=$arr['wtaxnote'];	
	
	$wtaxtype=$arr['wtaxtype'];
	$wtaxpay=$arr['wtaxpay'];

    $basevatamt=$arr['basevatamt'];	
	$basewtaxamt=$arr['basewtaxamt'];	

    $sale_type = $arr['sale_type'];	
	
	
	if ($sale_type=='VT') {
		$vatno=$arr['invno'];;
		$vatdate=$arr['invdate'];
		$vatmonth=$arr['vatmonth'];
		if ($vatmonth=='') {
			$vatmonth=substr($vatdate,-7);	
		}	
	} else {
		$vatno='';
		$vatdate='';
		$vatmonth='';
    }		
		
	/////////// customer detail	///////////////
	$billname=$arr['billname'];
	$addr1=$arr['addr1'];
	$addr2=$arr['addr2'];
	$addr3=$arr['addr3'];
	$province=$arr['province'];
	$custzip=$arr['custzip'];
	$custtel=$arr['custtel'];
	$taxid=$arr['taxid'];
	$branch=$arr['branch'];		
	///////////////////////////
	$creator = $arr['creator'];
    $datecreate = date('YmdHis');
	
	$old_docno = $arr['old_docno'];
	$old_totamt = $arr['old_totamt'];
	
	
	if ($old_totamt=='') {$old_totamt=0;}
	
	if ($discamt=='') {$discamt=0;}
	if ($creditdays=='') {$creditdays=0;}   
	if ($wtaxline=='') {$wtaxline=0;}   
	if ($basevatamt=='') {$basevatamt=0;}
	if ($basewtaxamt=='') {$basewtaxamt=0;}
	
	
	
	if ($totamt_calvat=='') {$totamt_calvat=0;}
	if ($totamt=='') {$totamt=0;}
	if ($discamt=='') {$discamt=0;}
    if ($vatamt=='') {$vatamt=0;}
    if ($totamt_incvat=='') {$totamt_incvat=0;}
	if ($wtaxamt=='') {$wtaxamt=0;}
	if ($netamt=='') {$netamt=0;}
	
	$cdocdate = substr($docdate,-4) . substr($docdate,3,2) . substr($docdate,0,2);
	$duedate2 = substr($duedate,-4) . substr($duedate,3,2) . substr($duedate,0,2); 
	$vatdate2 = substr($vatdate,-4) . substr($vatdate,3,2) . substr($vatdate,0,2); 
	$wtaxdate2 = substr($wtaxdate,-4) . substr($wtaxdate,3,2) . substr($wtaxdate,0,2); 
	
    mysql_query("BEGIN");	 
	
	if (($doctype=='B') && ($wtaxamt > 0) && ($wtaxdate <> '') && ($wtaxno=='')) {
		$document_number = get_run($link,'WT',$docdate,'',$comid,$whcode);
		if ($document_number==='ERROR') {
			$ret = array('result'=>0, 'msg'=>'error wtax running' );
			$success=false;					
			goto end_process;
		}
		$wtaxno=$document_number;
		
	}	

	
	
	if ($mode=='เพิ่ม') {
	
		if (($doctype=='P') or ($doctype=='S')) {
			$docvalue = 1;
		} else {
			$docvalue = -1;
		}
		//  ขายสด, ขายเชื่อ, ลดลูกหนี้
		if (($doctype=='A') || ($doctype=='S') || ($doctype=='R') ) {	

			$document_number = get_run($link,$sale_type,$docdate,'',$comid,$whcode);
			if ($document_number==='ERROR') {
				$ret = array('result'=>0, 'msg'=>'error vat running' );
				$success=false;					
				goto end_process;
			}
			if ($document_number <> '') {
				$invno=$document_number;
			}
			
			$invdate=$docdate;
			$invdate2=$cdocdate;

			if (($sale_type=='VT') || ($sale_type=='CN')) {
				$vatno=$document_number;
				$vatdate=$docdate;
				$vatdate2=$cdocdate;
				$vatmonth=substr($vatdate,-7);
			}	
		}
		

		$runno = get_run($link,'2' . $doctype,$docdate,'',$comid,$whcode);
	   
		if ($runno==='ERROR') {
		 
		   $ret = array('result'=>0, 'msg'=>'error running' );
		   $success=false;
		}  else {	
		   $docno=$runno;
		   $sql = "insert into ic ( 
				comid,doctype,refcode,docno,docdate,cdocdate,whcode,custcode,dptcode,projcode,emp_req,emp_approve,
						remark,remark2,remark3,remark4,remark5,remark6, 
						vattype,totamt_calvat,totamt,discamt,vatamt,totamt_incvat,wtaxamt,netamt, isstatus,isdelete,
						sale_to,creditdays,paytypecode,
						invno,invdate,docref,duedate,duedate2,sum_payamt,bal_payamt,sum_addamt,sum_decamt, docvalue,
						vatno,vatdate,vatdate2,vatmonth, wtaxno,wtaxdate,wtaxdate2,wtaxmonth,wtaxline,wtaxnote,
						wtaxtype,wtaxpay,
						basevatamt,basewtaxamt,sale_type,
						addr1,addr2,addr3,province,taxid,branch,billname,custzip,custtel,
						totamt_beforevat, gold_sal_b, gold_ret_b, gold_ret_g, gold_ret_amt, gold_vat_diff,	
					   creator,datecreate,isenable , old_docno, old_totamt ) 						
				values (
				'$comid','$doctype','$refcode','$runno','$docdate','$cdocdate','$whcode','$custcode','$dptcode','$projcode','$emp_req','$emp_approve',
						 '$remark','$remark2','$remark3','$remark4','$remark5','$remark6',
						'$vattype',$totamt_calvat,$totamt,$discamt,$vatamt,$totamt_incvat,$wtaxamt,$netamt,'1','0',
						'$sale_to',$creditdays,'$paytypecode',
						'$invno','$invdate','$docref','$duedate','$duedate2',$sum_payamt,$bal_payamt,$sum_addamt,$sum_decamt, $docvalue,
						'$vatno','$vatdate','$vatdate2','$vatmonth','$wtaxno','$wtaxdate','$wtaxdate2','$wtaxmonth',$wtaxline,'$wtaxnote',
						$wtaxtype,$wtaxpay,
						$basevatamt, $basewtaxamt,'$sale_type',
						'$addr1','$addr2','$addr3','$province','$taxid','$branch','$billname','$custzip','$custtel',
						$totamt_beforevat, $gold_sal_b, $gold_ret_b, $gold_ret_g, $gold_ret_amt, $gold_vat_diff,	
				'$creator','$datecreate',1, '$old_docno', $old_totamt ) ";
		
			if (!mysql_query($sql,$link)) {
				$ret = array('result'=>0, 'msg'=>$sql );
				$success=false;
			}				
		}		
	} else {
		$sql = "update ic set docdate='$docdate',
		                      cdocdate='$cdocdate',
							  refcode='$refcode',
							  whcode='$whcode',
							  custcode='$custcode',
							  dptcode='$dptcode',
							  projcode='$projcode',
							  emp_req='$emp_req',
							  emp_approve='$emp_approve',						 
							  remark='$remark',
							  remark2='$remark2',
							  remark3='$remark3',
							  remark4='$remark4',
							  remark5='$remark5',
							  remark6='$remark6',
							  vattype='$vattype',
							  totamt_calvat=$totamt_calvat,
							  totamt=$totamt,
							  discamt=$discamt,
							  totamt_beforevat=$totamt_beforevat,
							  gold_sal_b=$gold_sal_b,
							  gold_ret_b=$gold_ret_b,
							  gold_ret_g=$gold_ret_g,
							  gold_ret_amt=$gold_ret_amt,
							  gold_vat_diff=$gold_vat_diff,
							  vatamt=$vatamt,
							  totamt_incvat=$totamt_incvat,
							  wtaxamt=$wtaxamt,
							  netamt=$netamt,
							  sale_to='$sale_to',
							  creditdays=$creditdays,
							  paytypecode='$paytypecode',
							  invno='$invno',
							  invdate='$invdate',
							  docref='$docref',
							  duedate='$duedate',
							  duedate2='$duedate2',
							  sum_payamt=$sum_payamt,
							  bal_payamt=$bal_payamt,
							  sum_addamt=$sum_addamt,
							  sum_decamt=$sum_decamt,
							  vatno='$vatno',
							  vatdate='$vatdate',
							  vatdate2='$vatdate2',
							  vatmonth='$vatmonth',
							  wtaxno='$wtaxno',
							  wtaxdate='$wtaxdate',
							  wtaxdate2='$wtaxdate2',
							  wtaxmonth='$wtaxmonth',
							  wtaxnote='$wtaxnote',
							  wtaxline=$wtaxline,
							  wtaxtype=$wtaxtype,
							  wtaxpay=$wtaxpay,							  
							  basevatamt=$basevatamt,
							  basewtaxamt=$basewtaxamt,
							  sale_type='$sale_type',
							  addr1='$addr1',
							  addr2='$addr2',
							  addr3='$addr3',
							  province='$province',
							  taxid='$taxid',
							  branch='$branch',
							  billname='$billname',
							  custzip='$custzip',
							  custtel='$custtel',
							  old_docno = '$old_docno',
							  old_totamt = $old_totamt,
							  updateby='$creator',
							  dateupdate='$datecreate'
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
			
			
			if ($success) {
			
				$sql = "delete from ic_det where comid='$comid' and doctype='$doctype' and docno='$docno' ";
				if (!mysql_query($sql,$link)) {
					$ret = array('result'=>0, 'msg'=>$sql );
					$success=false;
				}	
			}
			
		}	
		if ($success) {
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




		
	}	
	
	if ($success) {
		if (isset($items)) {
			for ($i=0;$i< count($items);$i++) {
				$arr = $items[$i];	
				
				$itemcode = trim(strtoupper($arr['itemcode']));
				$unitcode = trim(strtoupper($arr['unitcode']));
				
				$qty = $arr['qty'];
				
				//=============== ระบบหลายหน่วย =============
				$unitstock = $unitcode;
				$unitratio = 1; 
				$qtystock = $qty;
				$sql = "select unitcode,packunit,packsize from item where comid='$comid' and itemcode='$itemcode' ";
				$res = mysql_query($sql,$link);
				if ($rs=mysql_fetch_array($res)) {
					$unitstock=$rs['unitcode'];
					if ( trim($rs['packunit'])==$unitcode) {
						$unitratio = $rs['packsize'];
						$qtystock = $qty * $unitratio;
					}	
				}	
				//===========================================
				
				
				
				
				$unitweight = $arr['unitweight'];
				$unitcost = $arr['unitcost'];			
				$beforedisc = $arr['beforedisc'];
				$disc = $arr['disc'];
				$discunit = $arr['discunit'];
				$discamt = $arr['discamt'];			
				$totamt = $arr['totamt'];
				
				$vat = $arr['vat'];
				$wtax = $arr['wtax'];
				
				$vatadj = $arr['vatadj'];
				$wtaxadj = $arr['wtaxadj'];
				
				
				$remark = $arr['remark'];
				$stock = $arr['stock'];	
				$loccode = strtoupper($arr['loccode']);	
				$projcode = strtoupper($arr['projcode']);
				$actcode = strtoupper($arr['actcode']);
				$jobcode = strtoupper($arr['jobcode']);			
				$ref_doctype = $arr['ref_doctype'];
				$ref_docno = $arr['ref_docno'];
				$ref_linenbr = $arr['ref_linenbr'];	
				/////////////////////////////////	
				
				$vattype = $arr['vattype'];
				$before_vatamt = $arr['before_vatamt'];
				$after_vatamt = $arr['after_vatamt'];
				$vatamt = $arr['vatamt'];
				$wtaxamt = $arr['wtaxamt'];
				if ($before_vatamt=='') {$before_vatamt=0;}
				if ($after_vatamt=='') {$after_vatamt=0;}
				if ($vatamt=='') {$vatamt=0;}
				if ($wtaxamt=='') {$wtaxamt=0;}
				
				////////////////////////////////////
				if ($unitcost=='') {$unitcost=0;}
				if ($totamt=='') {$totamt=0;}
				if ($disc=='') {$disc=0;}
				if ($discamt=='') {$discamt=0;}		
				
				if ($vatadj=='') {$vatadj=0;}	
				if ($wtaxadj=='') {$wtaxadj=0;}	

				$lot = strtoupper($arr['lot']);
				$itembarcode = $arr['itembarcode'];
				$docref_date = $arr['docref_date'];
				$docref_date2= substr($docref_date,-4) . substr($docref_date,3,2) . substr($docref_date,0,2);
				
				$linenbr = $i + 1;
				
				$stock=1;
				
				if (($doctype=='S') || ($doctype=='C') || ($doctype=='A') ) {
					$stock = -1;
				}
				
				$sql = "insert into ic_det ( comid,doctype,docno,linenbr,itemcode,unitcode,whcode,loccode,lot,
											qty,unitweight,unitcost,beforedisc,disc,discunit,discamt,totamt,
											vat,wtax,remark,projcode,actcode,jobcode,
											ref_doctype,ref_docno,ref_linenbr,stock,
											itembarcode,docref_date,docref_date2,
											vattype,before_vatamt,after_vatamt,vatamt,wtaxamt,
											vatadj,wtaxadj,
											unitstock,unitratio,qtystock)				
						values ('$comid','$doctype','$docno',$linenbr,'$itemcode','$unitcode','$whcode','$loccode','$lot',
											$qty,$unitweight,$unitcost,$beforedisc,$disc,'$discunit',$discamt,$totamt,
											'$vat','$wtax','$remark','$projcode','$actcode','$jobcode',
											'$ref_doctype','$ref_docno',$ref_linenbr,$stock,
											'$itembarcode','$docref_date','$docref_date2',
											'$vattype',$before_vatamt,$after_vatamt,$vatamt,$wtaxamt,
											$vatadj,$wtaxadj,
											'$unitstock',$unitratio,$qtystock) ";			
				if (!mysql_query($sql,$link)) {
					$ret = array('result'=>0, 'msg'=>$sql );
					$success=false;
					break;
				}				
				if ($ref_doctype=='PO') {
					$sql =  "update po_det set qty_clear = qty_clear +   $qty  
							where comid='$comid' and doctype='$ref_doctype' and docno='$ref_docno' and linenbr=$ref_linenbr ";						
					if (!mysql_query($sql,$link)) {
						$ret = array('result'=>0, 'msg'=>$sql );
						$success=false;
						break;
					}					
				}			
				if ($ref_doctype=='SO') {
					$sql =  "update so_det set qty_clear = qty_clear +   $qty  
							where comid='$comid' and doctype='$ref_doctype' and docno='$ref_docno' and linenbr=$ref_linenbr ";						
					if (!mysql_query($sql,$link)) {
						$ret = array('result'=>0, 'msg'=>$sql );
						$success=false;
						break;
					}					
				}
			}
		}	
	}
	////////////////////////////////  rev ///////////////////////////////////////////////////
	if ($success) {
		if (isset($_POST["revexps"])) {
			for ($i=0;$i< count($revexps);$i++) {
				$arr = $revexps[$i];
				
				$revcode = $arr['revcode'];
				$revamt = $arr['revamt'];
				$revexp = $arr['revexp'];
				$revnote = $arr['revnote'];	
				$projcode = strtoupper($arr['projcode']);				
						
				if ($revamt=='') {$revamt=0;}			
				$linenbr = $i + 1;			
				$sql = "insert into ic_rev ( comid,doctype,docno,linenbr,revcode,revamt,revexp,revnote,projcode)
			
						values ('$comid','$doctype','$docno',$linenbr,'$revcode',$revamt,$revexp,'$revnote','$projcode' ) ";
																								
				if (!mysql_query($sql,$link)) {
					$ret = array('result'=>0, 'msg'=>$sql );
					$success=false;
					break;
				}	
			}
		}	
	}	
	
	/////////////////////////////////////  payment ================================
	if ($success) {
		if (isset($_POST["payments"])) {
			for ($i=0;$i< count($payments);$i++) {
				$arr = $payments[$i];
				
				$paytypecode = $arr['paytypecode'];
				$projcode = strtoupper($arr['projcode']);
				
				$docref = $arr['docref'];
				$dateref = $arr['docdate'];
				
				$dateref2 = '';
				if ($dateref <> '') {
					$dateref2 = scr2dbdate($dateref);	
				}	
				
				$remark = $arr['remark'];			
				$amount = $arr['amount'];			
				if ($amount=='') {$amount=0;}			
				$linenbr = $i + 1;			
				$sql = "insert into ic_pay ( comid,doctype,docno,linenbr,paytypecode,docref,dateref,remark,amount, projcode, isstatus, dateref2, creator, datecreate )
			
						values ('$comid','$doctype','$docno',$linenbr,'$paytypecode','$docref','$dateref','$remark',$amount, '$projcode', 0 ,'$dateref2', '$creator','$datecreate') ";
																								
				if (!mysql_query($sql,$link)) {
					$ret = array('result'=>0, 'msg'=>$sql );
					$success=false;
					break;
				}	
			}
		}	
	}
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


