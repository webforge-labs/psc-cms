<?php

require_once 'fluidgrid/bootstrap.php';

$content = new FluidGrid_Container(
  new FluidGrid_Row(
    fg::grid(
      new FluidGrid_HTMLElement('h1',
        new FluidGrid_String('Newsl Administration'), 
        array('id'=>'branding')
      ))
    ->setWidth(16)),
  new FluidGrid_Row(fg::grid(
      new FluidGrid_Navigation(Array(
          array('name'=>'Fluid 12-column', 'url'=>'../../../12/',
            'subnav'=>array(
              array('name'=>'MooTools', 'url'=>'../../../12/fluid/mootools/'),
              array('name'=>'jQuery', 'url'=>'../../../12/fluid/jquery/'),
              array('name'=>'none', 'url'=>'../../../12/fluid/none/'),
            ),
          ),
          array('name'=>'Fluid 16-column', 'url'=>'../../../16/',
            'subnav'=>array(
              array('name'=>'MooTools', 'url'=>'../../../16/fluid/mootools/'),
              array('name'=>'jQuery', 'url'=>'../../../16/fluid/jquery/'),
              array('name'=>'none', 'url'=>'../../../16/fluid/none/'),
            ),
          ),
          array('name'=>'Fluid 12-column', 'url'=>'../../../12/',
            'subnav'=>array(
              array('name'=>'MooTools', 'url'=>'../../../12/fluid/mootools/'),
              array('name'=>'jQuery', 'url'=>'../../../12/fluid/jquery/'),
              array('name'=>'none', 'url'=>'../../../12/fluid/none/'),
            ),
          ),
          array('name'=>'Fluid 16-column', 'url'=>'../../../16/',
            'subnav'=>array(
              array('name'=>'MooTools', 'url'=>'../../../16/fluid/mootools/'),
              array('name'=>'jQuery', 'url'=>'../../../16/fluid/jquery/'),
              array('name'=>'none', 'url'=>'../../../16/fluid/none/'),
            ),
          ),
          array('name'=>'Fluid 12-column', 'url'=>'../../../12/',
            'subnav'=>array(
              array('name'=>'MooTools', 'url'=>'../../../12/fluid/mootools/'),
              array('name'=>'jQuery', 'url'=>'../../../12/fluid/jquery/'),
              array('name'=>'none', 'url'=>'../../../12/fluid/none/'),
            ),
          ),
          array('name'=>'Fluid 16-column', 'url'=>'../../../16/',
            'subnav'=>array(
              array('name'=>'MooTools', 'url'=>'../../../16/fluid/mootools/'),
              array('name'=>'jQuery', 'url'=>'../../../16/fluid/jquery/'),
              array('name'=>'none', 'url'=>'../../../16/fluid/none/'),
            ),
          ),
        ))
    )->setWidth(16)
  ),
	new FluidGrid_Row(
    fg::grid(new FluidGrid_HTMLElement('h2',new FluidGrid_String('Newsl Administration'), array('id'=>'page-heading')))->setWidth(16)
  ),
	/* content */
	new FluidGrid_Row(
    fg::menu(
      Array(
        array('name'=>'Menu 1',
          'subnav'=>Array(
            array('name'=>'Submenu 1.1','url'=>'/submenu1'),
            array('name'=>'Submenu 1.2','url'=>'/submenu2'),
            array('name'=>'Submenu 1.3','url'=>'/submenu3')
          )
        ),
        array('name'=>'Menu 2',
          'subnav'=>Array(
            array('name'=>'Submenu 2.1','url'=>'/submenu1'),
            array('name'=>'Submenu 2.2','url'=>'/submenu2'),
            array('name'=>'Submenu 2.3','url'=>'/submenu3')
          )
        )
      )
    )->setWidth(2),
    fg::grid(
      FluidGrid_Table::factory(
        Array(
          'heading'=>'Table Heading',
          'head'=>Array(array(FluidGrid_HTMLElement::factory('th',new FluidGrid_String('Column 1')),'Column 2','Column 3')),
          'body'=>Array(
            array('Lorem ipsum','Dolor sit','$ 125.00'),
            array('Dolor sit','Nostrud exerci','$ 75.00'),
            array('Nostrud exerci','Lorem ipsum','$200.00'),
            array('Dolor sit','Nostrud exerci','$ 175.00'),
          )
        )
      )
    ),
    fg::grid(FluidGrid_HTMLElement::factory('div')->setClass('box'))->setWidth(2)
  ),
	new FluidGrid_Row(
		fg::grid(
      fg::box(
        NULL,
        fg::s('<p>Fluid 960 Grid System, created by <a href="http://www.domain7.com/WhoWeAre/StephenBau.html">Stephen Bau</a>, based on the <a href="http://960.gs/">960 Grid System</a> by <a href="http://sonspring.com/journal/960-grid-system">Nathan Smith</a>. Released under the <a href="../../../licenses/GPL_license.txt">GPL</a> / <a href="../../../licenses/MIT_license.txt">MIT</a> <a href="../../../README.txt">Licenses</a>.</p>'),
        FALSE
      )
    )->setWidth(16)->setAttribute('id','site_info')
  ),
  new FluidGrid_Row(
    fg::grid(
      new FluidGrid_Form(
        
      )
    )
  )
);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Fluid 960 Grid System | 16-column Grid</title>
    <link rel="stylesheet" type="text/css" href="fluidgrid/css/reset.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="fluidgrid/css/text.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="fluidgrid/css/grid.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="fluidgrid/css/layout.css" media="screen" />

    <link rel="stylesheet" type="text/css" href="fluidgrid/css/nav.css" media="screen" />
    <!--[if IE 6]><link rel="stylesheet" type="text/css" href="fluidgrid/css/ie6.css" media="screen" /><![endif]-->
    <!--[if IE 7]><link rel="stylesheet" type="text/css" href="fluidgrid/css/ie.css" media="screen" /><![endif]-->
  </head>

<body>
<?= $content ?>

<script type="text/javascript" src="fluidgrid/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="fluidgrid/js/jquery-ui.js"></script>
<script type="text/javascript" src="fluidgrid/js/jquery-fluid16.js"></script>

</body>
</html>