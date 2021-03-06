<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Orders controller
 *
 * @author		Jamie Holdroyd
 * @author		Chris Harvey
 * @package		FireSale\Core\Controllers
 *
 */
class Front_orders extends Public_Controller
{

	public function __construct()
	{

		parent::__construct();
		
		// Add data array
		$this->data = new stdClass();

		// Load models, lang, libraries, etc.
		$this->load->model('routes_m');
		$this->load->model('orders_m');
		$this->load->model('categories_m');
		$this->load->model('products_m');

		// Load css/js
		$this->template->append_css('module::firesale.css')
					   ->append_js('module::firesale.js');

	}
	
	public function index()
	{
		
		// Variables
		$user = ( isset($this->current_user->id) ? $this->current_user->id : NULL );
		
		// Check user
		if( $user != NULL )
		{

			// Set query paramaters
			$params	 = array(
				'stream' 	=> 'firesale_orders',
				'namespace'	=> 'firesale_orders',
				'where'		=> "created_by = '{$user}'",
				'order_by'  => 'id',
				'sort'      => 'desc'
			);
		
			// Get entries		
			$orders = $this->streams->entries->get_entries($params);

			// Add items to order
			if( $orders['total'] > 0 )
			{
				foreach( $orders['entries'] AS $key => $order )
				{
					$products = $this->orders_m->order_products($order['id']);
					$orders['entries'][$key] = array_merge($orders['entries'][$key], $products);
				}
			}
			
			// Variables
			$this->data->total  	= $orders['total'];
			$this->data->orders 	= $orders['entries'];
			$this->data->pagination = $orders['pagination'];
		
			// Build page
			$this->template->title(lang('firesale:orders:my_orders'))
						   ->set_breadcrumb(lang('firesale:orders:my_orders'), $this->routes_m->build_url('orders'))
						   ->set($this->data);

			// Fire events
			Events::trigger('page_build', $this->template);

			// Build page
			$this->template->build('orders');
		
		}
		else
		{
			// Must be logged in
			$this->session->set_flashdata('error', lang('firesale:orders:logged_in'));
			redirect('users/login');
		}
	
	}
	
	public function view_order($id)
	{
	
		// Variables
		$user  = ( isset($this->current_user->id) ? $this->current_user->id : NULL );
		$order = $this->orders_m->get_order_by_id($id);

		// Check user can view
		if( $user != NULL AND $order != FALSE AND $user == $order['created_by']['user_id'] )
		{

			// Format order for display
			$order['price_sub']   = $this->currency_m->format_string($order['price_sub'], (object)$order['currency'], FALSE, FALSE);
			$order['price_ship']  = $this->currency_m->format_string($order['price_ship'], (object)$order['currency'], FALSE, FALSE);
			$order['price_total'] = $this->currency_m->format_string($order['price_total'], (object)$order['currency'], FALSE, FALSE);

			// Format products
			foreach( $order['items'] AS &$item )
			{
				$item['price'] = $this->currency_m->format_string($item['price'], (object)$order['currency'], FALSE, FALSE);
				$item['total'] = $this->currency_m->format_string($item['total'], (object)$order['currency'], FALSE, FALSE);
			}

			// Build page
			$this->template->title(sprintf(lang('firesale:orders:view_order'), $id))
						   ->set_breadcrumb('Home', '/')
						   ->set_breadcrumb(lang('firesale:orders:my_orders'), $this->routes_m->url('orders'))
						   ->set_breadcrumb(sprintf(lang('firesale:orders:view_order'), $id), $this->routes_m->url('orders-single', $id))
						   ->set($order);

			// Fire events
			Events::trigger('page_build', $this->template);

			// Build page
			$this->template->build('orders_single');

		}
		else
		{
			// Must be logged in
			$this->set_flashdata('error', lang('firesale:orders:logged_in'));
			redirect('users/login');
		}
	
	}

}
