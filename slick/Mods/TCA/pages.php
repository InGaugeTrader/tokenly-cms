<?php
/*
 * @name = CMS Page TCA
 * 
 * 
 * */
 
 
//add the new fields to page form
\Util\Filter::addFilter('App\CMS\Pages_Model', 'getPageForm', 
					function($form, $args){
						
						$token = new \UI\Textbox('access-token');
						$token->setLabel('Access Token');
						$form->add($token);
						
						$token_req = new \UI\Textbox('token-req');
						$token_req->setLabel('Minimum amount of token required for access');
						$token_req->setValue(1);
						$form->add($token_req);
						
						if(isset($args[0]) AND $args[0] > 0){
							//set values if the page already exists
							$meta = new \App\Meta_Model;
							$form->field('access-token')->setValue($meta->getPageMeta($args[0], 'access-token'));
							$form->field('token-req')->setValue($meta->getPageMeta($args[0], 'token-req'));
						}
						
						return $form;
					});


//edit page process code
\Util\Filter::addFilter('App\CMS\Pages_Model', 'editPage', 
						function($id, $data){
							$inventory = new \App\Tokenly\Inventory_Model;
							$meta = new \App\Meta_Model;
							$asset = false;
							$amount = 0;
							
							if(!isset($data['access-token']) OR !isset($data['token-req'])){
								return array($id, $data);
							}

							$meta->updatePageMeta($id, 'access-token', trim(strtoupper($data['access-token'])));
							$meta->updatePageMeta($id, 'token-req', trim($data['token-req']));
						
							$model = new \Core\Model;
							$page_module = get_app('pages.page-view');
							
							$user = user();
							
							$remove_locks = remove_tca_locks($page_module['moduleId'], $id, 'page');
							if(trim($data['access-token']) != ''){
								$parse_input = parse_tca_token($data['access-token']);
								$parse_amount = parse_tca_amount($data['token-req']);
								$add_locks = add_tca_locks($user, $page_module['moduleId'], $id, 'page', $parse_input, $parse_amount);
							}							
						
							//continue with rest of processing code
							return array($id, $data);
						}, true);
						
						
//add page processing code
\Util\Filter::addFilter('App\CMS\Pages_Model', 'addPage', 
						function($id, $args){
							$data = $args[0];
	
							$meta = new \App\Meta_Model;
							if(!isset($data['access-token']) OR !isset($data['token-req'])){
								return array($id, $data);
							}

							$meta->updatePageMeta($id, 'access-token', trim(strtoupper($data['access-token'])));
							$meta->updatePageMeta($id, 'token-req', trim($data['token-req']));
						
							$model = new \Core\Model;
							$page_module = get_app('pages.page-view');
							
							$user = user();
							
							if(trim($data['access-token']) != ''){
								$parse_input = parse_tca_token($data['access-token']);
								$parse_amount = parse_tca_amount($data['token-req']);	
								$add_locks = add_tca_locks($user, $page_module['moduleId'], $id, 'page', $parse_input, $parse_amount);
							}		
							
							return $id;
						});

//delete page processing code
\Util\Filter::addFilter('App\CMS\Pages_Model', 'deletePage', 
			function($id){
				$page_module = get_app('pages.page-view');
				$model = new \Core\Model;
				$model->sendQuery('DELETE FROM token_access
								  WHERE moduleId = :moduleId
								  AND itemId = :itemId
								  AND itemType = "page"',
								  array(':moduleId' => $page_module['moduleId'],
										':itemId' => $id));
				return $id;
		}, true);


