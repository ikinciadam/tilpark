<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function add_user($data)
{
	$ci =& get_instance();
	
	// Have user email?
	$ci->db->where('status', '1');
	$ci->db->where('email', $data['email']);
	$query = $ci->db->get('users')->result_array();
	if($query)
	{
		return get_lang('E-mail address is registered.');
		return false;
	}
	
	
	// add user card
	$ci->db->insert('users', array(
		'email'=>$data['email'],
		'name'=>$data['name'],
		'surname'=>$data['surname'],
		'password'=>md5($data['password']),
		'role'=>$data['role']
	));
	return $ci->db->insert_id();
}

function update_user($user_id, $data)
{
	$ci =& get_instance();
	$array = array();
	
	$ci->db->where('id', $user_id);
	$ci->db->update('users', $data);
	return true;
}




function get_user($data)
{
	$ci =& get_instance();
	if(is_array($data))
	{
		if(isset($data['password'])) {unset($data['password']);}
		$ci->db->where($data);
	}
	else
	{
		$ci->db->where('id', $data);
	}
	$query = $ci->db->get('users')->row_array();
	if($query)
	{
		$query['display_name'] = $query['name'].' '.$query['surname'];
		$query['barcode'] = $query['id'];
		$query['name_surname'] = $query['name'].' '.$query['surname'];

		if($query['avatar'] == '')
		{
			$query['avatar'] = 'avatar.png';
		}
		return $query;
	}
	else{return false;}
}


function get_role_name($role)
{
	if($role == 5){ $user['role_name'] = 'Personel'; }
	else if($role == 4){ $user['role_name'] = 'Kıdemli Personel'; }
	else if($role == 3){ $user['role_name'] = 'Birim Amiri'; }
	else if($role == 2){ $user['role_name'] = 'Admin'; }
	else if($role == 1){ $user['role_name'] = 'Super Admin'; }
	return $user['role_name'];
}




function get_the_current_user($data)
{
	$ci =& get_instance();
	$user = $ci->session->userdata('user');
	return $user[$data];
}

function the_current_user($data)
{
	echo get_the_current_user($data);
}




/**
* get_users()
*
* @author	: Mustafa TANRIVERDI
* @email	: thetanriverdi@gmail.com
* @website  : www.tilpark.com
*
* Veritabanındaki bütün kullanıcıların listesini dizi halinde döndürür
* $data=array() dizi halinde parametre alır
*/
function get_users($data=array())
{
	$ci =& get_instance();
	if(isset($data['status'])) { $ci->db->where('status', $data['status']); }
	if(isset($data['role'])) { $ci->db->where('role', $data['role']); }
	if(isset($data['order_by'])) { $ci->db->order_by($data['order_by']); }
	$query = $ci->db->get('users')->result_array();
	$users = array();
	
	if($query)
	{
		if(isset($data['result_array'])) { return $query; }
		else
		{
			foreach($query as $q)
			{
				$users[$q['id']] = $q;
			}
			return $q;
		}
	}
	else
	{
		return false;
	}
}



/**
* get_users()
*
* @author	: Mustafa TANRIVERDI
* @email	: thetanriverdi@gmail.com
* @website  : www.tilpark.com
*
* Bu fonksiyon veritabanındaki bütün kullanıcları sorgular ve düzenli bir dizi halinde gönderir.
*/
function get_user_list()
{
	$users = array();
	$i=0;
	$ci =& get_instance();
	$query = $ci->db->get('users')->result_array();
	foreach($query as $user)
	{
		$users[$user['id']] = $user;
		$users[$user['id']]['name_surname'] = $user['name'].' '.$user['surname'];

		if($users[$user['id']]['avatar'] == '')
		{
			$users[$user['id']]['avatar'] = 'avatar.png';
		}
	}
	
	return $users;
}




/**
* is_admin()
*
* @author	: Mustafa TANRIVERDI
* @email	: thetanriverdi@gmail.com
* @website  : www.tilpark.com
*
* Şu anda aktif olan kullanının "süperadmin" veya "admin" olduğunu denetler. Eğer admin yetkisine sahip ise "true" döner. aksi durumda faslse döner.
*/
function is_admin($user_id='')
{
	if($user_id == '')
	{
		if(get_the_current_user('role') < 3)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{
		$user = get_user($user_id);
		
		if($user['role'] < 3)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}




/* ========================================================================
	 BİLDİRİM YÖNTETİCİSİ
	 yeni gelen bildirimler buradan yönetilmektedir
* ====================================================================== */

/**
* add_notification()
*
* @author	: Mustafa TANRIVERDI
* @email	: thetanriverdi@gmail.com
* @website  : www.tilpark.com
*
* Yeni bildirim olusturur
*/
function add_notification($data)
{
	$ci=& get_instance();
	$ci->db->insert('messagebox', $data);
	return $ci->db->insert_id();
}



/**
* notification_task()
*
* @author	: Mustafa TANRIVERDI
* @email	: thetanriverdi@gmail.com
* @website  : www.tilpark.com
*
* Eğer bir görev atanmış ve görev bitirme tarihi geçmiş ise bildirim oluşturur.
*/
function notification_task()
{
	$ci =& get_instance();
	
	$ci->db->where('status', '1');
	$ci->db->where('type', 'task');
	$ci->db->where('date_end <', date('Y-m-d H:i:s'));
	$ci->db->where('messagebox_id', '0');
	$ci->db->where('receiver_user_id', get_the_current_user('id'));
	$ci->db->where('onoff', '0');
	$query = $ci->db->get('messagebox')->result_array();
	foreach($query as $q)
	{	
		$data['type'] = 'noti';
		$data['receiver_user_id'] = get_the_current_user('id');
		$data['title'] = 'Görev süresi geçti: '.$q['title'];
		$data['content'] = 'user/task/'.$q['id'];
		$data['read'] = '0';
		$data['read_id'] = get_the_current_user('id');
		
		// bu bildirim daha onceden eklenmis mi?
		$ci->db->where('messagebox_id', '0');
		$ci->db->where('receiver_user_id', $data['receiver_user_id']);
		$ci->db->where('content', $data['content']);
		$is_notification = $ci->db->get('messagebox')->num_rows();
		
		if($is_notification == 0)
		{
			add_messagebox($data);
		}
	}
}





/* ========================================================================
 * MESSAGEBOX

	@author : Mustafa TANRIVERDI
	@E-mail : thetanriverdi@gmail.com

buradaki fonksiyonlar ile mesaj gönderebilirsiniz.

 * ===================================================================== */

function add_messagebox($data)
{
	$ci =& get_instance();

	if(!isset($data['type'])){ $data['type'] = 'message'; }
	$data['date'] = date('Y-m-d H:i:s');
	$data['read'] = '0';
	$data['read_id'] = $data['receiver_user_id'];
	$data['date_update'] = date('Y-m-d H:i:s');


	# baslik yok ise content ile yeni baslik olustur
	if(@strlen($data['title']) < 1)
	{
		$data['title'] = strip_tags($data['content']);
	}
	
	if(isset($data['messagebox_id']))
	{
		$message = get_message($data['messagebox_id']);
		
		$data['title'] = $message['title'];
	
		$ci->db->where('id', $data['messagebox_id']);	
		$ci->db->update('messagebox', array('delete_sender'=>'1', 'delete_receiver'=>'1'));
		
		$ci->db->where('messagebox_id', $data['messagebox_id']);	
		$ci->db->update('messagebox', array('delete_sender'=>'1', 'delete_receiver'=>'1'));
	}


	$query = $ci->db->insert('messagebox', $data);
	if($query)
	{
		return $ci->db->insert_id();
	}
	else
	{
		return false;
	}

}



/* gelen mesajları/görevleri/bildirimleri liste halinde sunar */
function get_messagebox($data)
{
	$ci =& get_instance();
	
	// eger status yok ise status 1 yap
	if(!isset($data['status'])){ $data['status'] = '1'; }
	
	
	$ci->db->where('type', $data['type']);
	if(isset($data['outbox']))
	{
		$ci->db->where('sender_user_id', get_the_current_user('id'));
		$ci->db->where('delete_sender', '1');
	}
	else
	{
		$ci->db->where('receiver_user_id', get_the_current_user('id'));
		$ci->db->where('delete_receiver', '1');
	}
	
	if(isset($data['top_message'])){ $ci->db->where('messagebox_id', '0'); }
	if(isset($data['onoff'])){ $ci->db->where('onoff', $data['onoff']); }
	if(isset($data['read_id'])){ $ci->db->where('read_id', $data['read_id']); }
	if(isset($data['order_by'])){ $ci->db->order_by($data['order_by']); }
	if(isset($data['limit'])){ $ci->db->limit($data['limit']); }
	

	$query = $ci->db->get('messagebox')->result_array();
	if($query)
	{
		return $query;
	}
	else
	{
		return false;
	}
}

function get_message($data)
{
	$ci =& get_instance();
	if(is_array($data))
	{
		$ci->db->where($data);
	}
	else
	{
		$ci->db->where('id', $data);
	}
	$query = $ci->db->get('messagebox')->row_array();
	if($query)
	{
		return $query;
	}
	else
	{
		return false;
	}
}







/* yeni kac tane mesaj */
function calc_message($type='message')
{
	$ci =& get_instance();

	// eger status yok ise status 1 yap
	if(!isset($data['status'])){ $data['status'] = '1'; }
	
	$ci->db->where('type', $type);
	$ci->db->where('read_id', get_the_current_user('id'));
	$ci->db->where('read', '0');
	$query = $ci->db->get('messagebox')->result_array();

	$i = 0;
	$is_message = array();
	foreach($query as $q)
	{
		if($q['messagebox_id'] > 0){ $q['id'] = $q['messagebox_id']; }
		if(isset($is_message[$q['id']])){}
		else
		{
			$is_message[$q['id']] = true;
			$i++;
		}
	}
	return $i;
}




function add_log($data)
{
	$ci =& get_instance();
	$data['user_id'] = get_the_current_user('id'); 
	if(isset($_POST['log_time']))
	{
		$data['microtime'] = $_POST['log_time'];
	}
	elseif(isset($_GET['log_time']))
	{
		$data['microtime'] = str_replace('%20', ' ', $_GET['log_time']);
	}
	
	if(isset($_POST['microtime']))
	{
		$data['microtime'] = $_POST['microtime'];
	}
	elseif(isset($_GET['microtime']))
	{
		$data['microtime'] = str_replace('%20', ' ', $_GET['microtime']);
	}
	
	$data['date'] = date('Y-m-d H:i:s');
	$data['ip'] = $ci->input->ip_address();
	$data['browser'] = $ci->agent->browser();
	$data['platform'] = $ci->agent->platform();
	
	$ci->db->insert('user_logs', $data);
}

function is_log()
{
	$ci =& get_instance();
	
	if(isset($_POST['log_time']))
	{
		$data['microtime'] = $_POST['log_time'];
	}
	elseif(isset($_GET['log_time']))
	{
		$data['microtime'] = str_replace('%20', ' ', $_GET['log_time']);
	}
	
	if(isset($_POST['microtime']))
	{
		$data['microtime'] = $_POST['microtime'];
	}
	elseif(isset($_GET['microtime']))
	{
		$data['microtime'] = str_replace('%20', ' ', $_GET['microtime']);
	}
	
	$ci->db->where('microtime', $data['microtime']);
	$ci->db->where('user_id', get_the_current_user('id'));
	$query = $ci->db->get('user_logs')->result_array();
	if($query){ return false; } else {return true; }
}


function get_log_table($data, $order_by='ASC', $array=array())
{
	$ci =& get_instance();
	
	if(!isset($array['user'])){$array['user'] = true;}
	if(!isset($array['form_id'])){$array['form_id'] = true;}
	?>
    
    <script>
	$(document).ready(function(e) {
        $("#form_log_1").validate();
    });
	</script>
    
    <?php
	if(isset($_POST['add_log_user_reviews']) and is_log())
	{
		$log['date'] = $ci->input->post('log_time');
		$log['type'] = 'user_reviews';
		$log['title']	= get_lang('User Reviews');
		$log['description'] = $ci->input->post('description');
		$log['user_id'] = get_the_current_user('id');
		
		if(isset($data['other_id'])) { $log['other_id'] = $data['other_id']; }
		if(isset($data['form_id'])) { $log['form_id'] = $data['form_id']; }
		if(isset($data['product_id'])) { $log['product_id'] = $data['product_id']; }
		if(isset($data['account_id'])) { $log['account_id'] = $data['account_id']; }
		
		add_log($log);
	}
	
	if(isset($_POST['add_log_user_reviews']))
	{
		?>
        <script>
		$(document).ready(function(e) {
			$("#tab_log").click();
		});
		</script>
        <?php
	}
	
	?>
    
    <form name="form_log_1" id="form_log_1" action="?" method="POST">
    	<div class="row">
        	<div class="col-md-10">
                <div class="form-group">
                    <label for="log_user_description" class="control-label ff-1 fs-16"><?php lang('User Reviews'); ?></label>
                    <div class="input-prepend input-group">
                        <span class="input-group-addon"><span class="glyphicon glyphicon-pencil"></span></span>
                        <input type="text" id="log_user_description" name="description" class="form-control input-lg ff-1 required" minlength="3" maxlength="2000">
                    </div>
                </div> <!-- /.form-group -->
    		</div> <!-- /.col-md-10 -->
            <div class="col-md-2">
            	<div class="form-group">
                	<input type="hidden" name="log_time" value="<?php echo date("Y-m-d H:i:s"); ?>" />
                    <input type="hidden" name="log_type" value="user_reviews" />
        			<input type="hidden" name="add_log_user_reviews" />
                    <label for="log_user_description" class="control-label ff-1 fs-16">&nbsp;</label>
                    <br />
                    <button class="btn btn-default2 btn-lg btn-block"><?php lang('Add'); ?></button>
                </div> <!-- /.form-group -->
            </div>
    	</div> <!-- /.row -->
    </form>
    
    
	<table class="table table-hover table-bordered table-condensed table-striped dataTable_noExcel_noLength">
            <thead>
                <tr>
                	<th class="hide"></th>
                    <th width="130"><?php lang('Date'); ?></th>
                    <?php if($array['user']==true):?><th width="200"><?php lang('User'); ?></th><?php endif; ?>
                    <?php if($array['form_id']==true):?><th width="100">Form ID</th><?php endif; ?>
                    <th><?php lang('Title'); ?></th>
                    <th><?php lang('Description'); ?></th>
                </tr>
            </thead>
            <tbody>
        <?php
		$users = get_user_list();
		$ci->db->where($data);
		$ci->db->order_by('id', $order_by);
		$query = $ci->db->get('user_logs')->result_array();
		foreach($query as $log):
		?>
        <tr>
        	<td class="hide"></td>
        	<td><?php echo substr($log['date'],0,16); ?></td>
            <?php if($array['user']==true):?><td><a href="<?php echo site_url('user/profile/'.$log['user_id']); ?>" target="_blank"><?php echo $users[$log['user_id']]['name'].' '.$users[$log['user_id']]['surname']; ?></a></td><?php endif; ?>
            <?php if($array['form_id']==true):?><td><?php if($log['form_id'] > 0):?><a href="<?php echo site_url('form/view/'.$log['form_id']); ?>" target="_blank">#<?php echo $log['form_id']; ?></a><?php endif; ?></td><?php endif; ?>
            <td><?php echo $log['title']; ?></td>
            <td><?php echo $log['description']; ?></td>
        </tr>
        <?php endforeach; ?>
        	</tbody>
        </table>	
        <?php
}








function calc_inbox()
{
	$ci =& get_instance();
	$ci->db->where('status', 1);
	$ci->db->where_in('type', array('message','reply_message'));
	$ci->db->where('inbox_view', '1');
	$ci->db->where('receiver_id', get_the_current_user('id'));		
	$ci->db->where('read', '['.get_the_current_user('id').']');
	$query = $ci->db->get('user_mess')->num_rows();
	
	return $query;
}


















function add_task($data)
{
	$ci =& get_instance();
	$data['recent_activity'] = date("Y-m-d H:i:s");
	if(!isset($data['date'])){$data['date'] = date("Y-m-d H:i:s");}
	if(!isset($data['type'])){$data['type'] = 'task';}
	if(!isset($data['sender_id'])){$data['sender_id'] = get_the_current_user('id');}
	if(!isset($data['read'])){$data['read'] = '['.$data['receiver_id'].']';}
	
	if($data['type'] == 'reply_task')
	{	
		$data['title'] = 'RE: '.$data['title'];
		
		$ci->db->where('top_id', $data['top_id']);
		$ci->db->update('user_mess', array('inbox_view'=>'0'));
		
		$ci->db->where('id', $data['top_id']);
		$query = $ci->db->get('user_mess')->row_array();
		
		// top_id recent activty date updated
		$ci->db->where('id', $data['top_id']);
		$ci->db->update('user_mess', array('recent_activity'=>$data['recent_activity']));

		
		if($query['sender_id'] == get_the_current_user('id'))
		{
			// top_id recent activty date updated
			$ci->db->where('id', $data['top_id']);
			$ci->db->update('user_mess', array('inbox_view'=>'0', 'recent_activity'=>$data['recent_activity']));
		}
	}
	
	$data['recent_activity'] = date("Y-m-d H:i:s");
	$ci->db->insert('user_mess', $data);
	return $ci->db->insert_id();
}


function calc_task()
{
	$ci =& get_instance();
	$ci->db->where('status', 1);
	$ci->db->where_in('type', array('task','reply_task'));
	$ci->db->where('inbox_view', '1');
	$ci->db->where('receiver_id', get_the_current_user('id'));		
	$ci->db->where('read', '['.get_the_current_user('id').']');
	$query = $ci->db->get('user_mess')->num_rows();
	
	return $query;
}





?>