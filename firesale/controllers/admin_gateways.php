<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_gateways extends Admin_Controller
{
	public $section = 'gateways';
	
	public function __construct()
	{
		parent::__construct();
		
		// Load the payments library
		$this->load->library('gateways');
		
		// Add metadata
		$this->template->append_css('module::gateways.css');
	}
	
	// Show installed
	public function index()
	{
		$data['gateways'] = $this->gateways->get_installed();

		// Build the page
		$this->template->title(lang('firesale:title') . ' ' . lang('firesale:sections:gateways'))
					   ->build('admin/gateways/index', $data);
	}
	
	// Show uninstalled
	public function add()
	{
		$data['gateways'] = $this->gateways->get_uninstalled();

		$this->template->build('admin/gateways/install', $data);
	}

	public function install($slug)
	{
		$fields = $this->gateways->get_setting_fields($slug);
		$rules = array(
			array(
				'field'	=> 'name',
				'label'	=> lang('firesale:gateways:labels:name'),
				'rules'	=> 'trim|htmlspecialchars|required|max_length[100]',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'desc',
				'label'	=> lang('firesale:gateways:labels:desc'),
				'rules'	=> 'trim|xss_clean|required',
				'type'	=> 'text'
			)
		);
		
		$this->lang->load('gateways');
		$values['name'] = lang('firesale:gateways:'.$slug.':name');
		$values['desc'] = lang('firesale:gateways:'.$slug.':desc');

		if (is_array($fields))
		{
			foreach ($fields as $field)
			{
				$field_data['field'] = $field['slug'];
				$field_data['label'] = $field['name'];
				
				if ($field['type'] == 'boolean')
				{
					$field_data['rules'] = 'required|callback__valid_bool';
					$field_data['type'] = 'boolean';
				}
				else
				{
					$field_data['rules'] = 'required|xss_clean|trim';
					$field_data['type'] = 'string';
				}
				
				$rules[] = $field_data;
				$additional_fields[] = $field_data;
			}
			
			$this->form_validation->set_rules($rules);
			
			if ($this->form_validation->run())
			{
				$data = array(
					'slug'			 => $slug,
					'name'			 => set_value('name'),
					'desc'			 => set_value('desc'),
					'created' 		 => date("Y-m-d H:i:s"),
					'ordering_count' => 0,
				);
				
				$this->db->trans_begin();
				$this->db->insert('firesale_gateways', $data);
				
				$gateway_id = $this->db->insert_id();
				
				foreach ($additional_fields as $field)
				{
					$this->db->insert('firesale_gateway_settings', array(
						'id'	=> $gateway_id,
						'key'	=> $field['field'],
						'value'	=> set_value($field['field'])
					));
				}
				
				
				
				if ($this->db->trans_status() !== FALSE)
				{
					$this->db->trans_commit();
					$this->session->set_flashdata('success', set_value('name').' was successfully installed');
					redirect('admin/firesale/gateways');
				}
				else
				{
					$this->db->trans_rollback();
					$this->session->set_flashdata('error', set_value('name').' could not be installed');
					redirect('admin/firesale/gateways/add');
				}
			}
			else
			{
				$this->template->build('admin/gateways/edit', array('fields' => $rules, 'values' => $values));
			}
		}
		else
		{
			show_404();
		}
	}

	public function _valid_bool($value)
	{
		if ($value == 1 OR $value == 0)
			return TRUE;
		
		$this->form_validation->set_message('_valid_bool', lang('firesale:gateways:errors:invalid_bool'));
		return FALSE;
	}
	
	public function enable($id)
	{
		if ($this->db->update('firesale_gateways', array('enabled' => 1), array('id' => (int)$id)))
		{
			$this->session->set_flashdata('success', 'The gateway has been enabled');
			redirect('admin/firesale/gateways');
		}
		else
		{
			$this->session->set_flashdata('error', 'That gateway could not be enabled');
			redirect('admin/firesale/gateways');
		}
	}
	
	public function disable($id)
	{
		if ($this->db->update('firesale_gateways', array('enabled' => 0), array('id' => (int)$id)))
		{
			$this->session->set_flashdata('success', 'The gateway has been disabled');
			redirect('admin/firesale/gateways');
		}
		else
		{
			$this->session->set_flashdata('error', 'That gateway could not be disabled');
			redirect('admin/firesale/gateways');
		}
	}
	
	public function uninstall($id)
	{
		$this->db->trans_begin();
		$this->db->delete('firesale_gateways', array('id' => (int)$id));
		$this->db->delete('firesale_gateway_settings', array('id' => (int)$id));
		
		if ($this->db->trans_status() !== FALSE)
		{
			$this->db->trans_commit();
			$this->session->set_flashdata('success', 'The gateway has been uninstalled');
			redirect('admin/firesale/gateways');
		}
		else
		{
			$this->db->trans_rollback();
			$this->session->set_flashdata('error', 'That gateway could not be uninstalled');
			redirect('admin/firesale/gateways');
		}
	}
	
	public function edit($slug)
	{
		$query = $this->db->get_where('firesale_gateways', array('slug' => $slug));
		
		if ($query->num_rows())
		{
			$values['name'] = $query->row()->name;
			$values['desc'] = $query->row()->desc;
											
			$fields = $this->gateways->get_setting_fields($slug);
			$rules = array(
				array(
					'field'	=> 'name',
					'label'	=> lang('firesale:gateways:labels:name'),
					'rules'	=> 'trim|htmlspecialchars|required|max_length[100]',
					'type'	=> 'string'
				),
				array(
					'field'	=> 'desc',
					'label'	=> lang('firesale:gateways:labels:desc'),
					'rules'	=> 'trim|xss_clean|required',
					'type'	=> 'text'
				)
			);
	
			if (is_array($fields))
			{
				foreach ($fields as $field)
				{
					$values[$field['slug']] = $this->gateways->setting($slug, $field['slug']);
					
					$field_data['field'] = $field['slug'];
					$field_data['label'] = $field['name'];
					
					if ($field['type'] == 'boolean')
					{
						$field_data['rules'] = 'required|callback__valid_bool';
						$field_data['type'] = 'boolean';
					}
					else
					{
						$field_data['rules'] = 'required|xss_clean|trim';
						$field_data['type'] = 'string';
					}
					
					$rules[] = $field_data;
					$additional_fields[] = $field_data;
				}
				
				$this->form_validation->set_rules($rules);
				
				if ($this->form_validation->run())
				{
					$data = array(
						'name'	=> set_value('name'),
						'desc'	=> set_value('desc')
					);
					
					$gateway_id = $this->db->get_where('firesale_gateways', array('slug' => $slug))->row()->id;
					
					$this->db->trans_begin();
					$this->db->update('firesale_gateways', $data, array('id' => $gateway_id));
										
					foreach ($additional_fields as $field)
					{
						if ($this->db->get_where('firesale_gateway_settings', array('id' => $gateway_id, 'key' => $field['field']))->num_rows())
						{
							$this->db->update('firesale_gateway_settings', array(
								'value'	=> set_value($field['field'])
							), array(
								'id'	=> $gateway_id,
								'key'	=> $field['field']
							));
						}
						else
						{
							$this->db->insert('firesale_gateway_settings', array(
								'id'	=> $gateway_id,
								'key'	=> $field['field'],
								'value'	=> set_value($field['field'])
							));
						}
					}
					
					
					
					if ($this->db->trans_status() !== FALSE)
					{
						$this->db->trans_commit();
						$this->session->set_flashdata('success', set_value('name').' was successfully updated');
						redirect('admin/firesale/gateways');
					}
					else
					{
						$this->db->trans_rollback();
						$this->session->set_flashdata('error', set_value('name').' could not be updated');
						redirect('admin/firesale/gateways');
					}
				}
				else
				{
					$this->template->build('admin/gateways/edit', array('fields' => $rules, 'values' => $values));
				}
			}
			else
			{
				show_404();
			}
		}
	}
}
