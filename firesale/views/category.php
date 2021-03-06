
      <div class="firesale width-onefourth sidebar left">
        <h2>Categories</h2>
        <ul class="icon-arrow categories">
{{ firesale:categories limit="0" }}
{{ if parent == id }}
          <li>
            <a href="{{ firesale:url route="category" id=id }}"><strong>{{ title }}</strong></a>
            <ul>
{{ firesale:sub_categories category=parent limit="0" }}
              <li><a href="{{ firesale:url route="category" id=id }}">{{ if category.id == id }}<strong>{{ title }}</strong>{{ else }}{{ title }}{{ endif }}</a></li>
{{ /firesale:sub_categories }}
            </ul>
          </li>
{{ else }}
          <li><a href="{{ firesale:url route="category" id=id }}">{{ title }}</a></li>
{{ endif }}
{{ /firesale:categories }}
        </ul>
      </div>

      <div class="firesale width-threefourth right last">

        <section id="listing-header">
		
		      <div class="left">
            <a href="{{ firesale:url route="category-custom" }}style/grid" class="grid{{ if layout == 'grid' }} selected{{ endif }}"><span class="icon"></span><?php echo lang('firesale:categories:grid'); ?></a>
            <a href="{{ firesale:url route="category-custom" }}style/list" class="list{{ if layout == 'list' }} selected{{ endif }}"><span class="icon"></span><?php echo lang('firesale:categories:list'); ?></a>
          </div>
		  
          <div class="right">
            <div id="listing-sort" class="switcher">
              <span>{{ order.title }}</span>
              <ul>
{{ ordering }}
                <li><a href="{{ firesale:url route="category-custom" }}order/{{ key }}">{{ title }}</a></li>
{{ /ordering }}
              </ul>
            </div>
          </div>

          <br class="clear" />
        </section>

        {{ pagination.links }}
	
        <section id="listing" class="{{ layout }}">

{{ if products }}
{{ products }}
          <article>
{{ if image == null }}
            <div class="no_image_180"></div>
{{ else }}
            <a href="{{ firesale:url route="product" id=id }}"><img src="{{ url:site }}files/thumb/{{ image }}/180/180" alt="{{ title }}" /></a>
{{ endif }}
            <section class="price-round medium"><span class="rrp">{{ if rrp > price }}{{ rrp }}{{ endif }}</span><span class="price">{{ price_formatted }}</span></section>
            <header>
              <h3><a href="{{ firesale:url route="product" id=id }}">{{ title }}</a></h3>
              <em>{{ code }}</em>
            </header>
            <p class="description">{{ helper:substr string=description start="0" end="250" }}...</p>
            <footer>
              <a href="{{ firesale:url route="cart" }}/insert/{{ id }}/1" class="basket"><span class="icon"></span><?php echo lang('firesale:categories:add_to_basket'); ?></a>
            </footer>
            <br class="clear" />
          </article>

{{ /products }}
{{ else }}
          <center style="margin-top: 135px"><h3><?php echo lang('firesale:prod_none'); ?></h3></center>
{{ endif }}

          <br class="clear" />
        </section>

        {{ pagination.links }}        

      </div>

      <br class="clear" />
