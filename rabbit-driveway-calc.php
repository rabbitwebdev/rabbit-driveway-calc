<?php
   /*
   Plugin Name: Driveway Cost Calculator
   Description: A custom plugin to manage driveway pricing and calculations.
   Version: 3.0
   Author: Your Name
   */

   // Register the settings page to manage prices
   function driveway_calculator_admin_menu() {
       add_menu_page(
           'Driveway Cost Settings',
           'Driveway Cost Calculator',
           'manage_options',
           'driveway-calculator-settings',
           'driveway_calculator_settings_page'
       );
   }
   add_action('admin_menu', 'driveway_calculator_admin_menu');

   // Render the settings page
   function driveway_calculator_settings_page() {
       // Code for rendering form fields for material/labor cost inputs
       echo '<h1>Driveway Cost Settings</h1>';
       // Add input fields for prices here

     if ($_POST['submit']) {
           update_option('driveway_asphalt_material_cost', $_POST['asphalt_material_cost']);
           update_option('driveway_asphalt_labor_cost', $_POST['asphalt_labor_cost']);
           update_option('driveway_concrete_material_cost', $_POST['concrete_material_cost']);
           update_option('driveway_concrete_labor_cost', $_POST['concrete_labor_cost']);
           update_option('driveway_gravel_material_cost', $_POST['gravel_material_cost']);
           update_option('driveway_gravel_labor_cost', $_POST['gravel_labor_cost']);
           update_option('driveway_blockpaving_material_cost', $_POST['blockpaving_material_cost']);
           update_option('driveway_blockpaving_labor_cost', $_POST['blockpaving_labor_cost']);
           update_option('blockpaving_herringbone_design_cost', $_POST['herringbone_design_cost']);
           update_option('blockpaving_basketweave_design_cost', $_POST['basketweave_design_cost']);
       }

       echo '<form method="POST">';
       
       echo '<h2>Asphalt Pricing</h2>';
       echo '<label>Material Cost per sqm</label>';
       echo '<input type="number" name="asphalt_material_cost" value="' . get_option('driveway_asphalt_material_cost') . '"/>';
       echo '<br/><label>Labor Cost per sqm</label>';
       echo '<input type="number" name="asphalt_labor_cost" value="' . get_option('driveway_asphalt_labor_cost') . '"/>';

       echo '<h2>Concrete Pricing</h2>';
       echo '<label>Material Cost per sqm</label>';
       echo '<input type="number" name="concrete_material_cost" value="' . get_option('driveway_concrete_material_cost') . '"/>';
       echo '<br/><label>Labor Cost per sqm</label>';
       echo '<input type="number" name="concrete_labor_cost" value="' . get_option('driveway_concrete_labor_cost') . '"/>';

       echo '<h2>Gravel Pricing</h2>';
       echo '<label>Material Cost per sqm</label>';
       echo '<input type="number" name="gravel_material_cost" value="' . get_option('driveway_gravel_material_cost') . '"/>';
       echo '<br/><label>Labor Cost per sqm</label>';
       echo '<input type="number" name="gravel_labor_cost" value="' . get_option('driveway_gravel_labor_cost') . '"/>';

       echo '<h2>Block Paving Pricing</h2>';
       echo '<label>Material Cost per sqm</label>';
       echo '<input type="number" name="blockpaving_material_cost" value="' . get_option('driveway_blockpaving_material_cost') . '"/>';
       echo '<br/><label>Labor Cost per sqm</label>';
       echo '<input type="number" name="blockpaving_labor_cost" value="' . get_option('driveway_blockpaving_labor_cost') . '"/>';

       echo '<h2>Block Paving Designs</h2>';
       echo '<label>Herringbone Design Cost per sqm</label>';
       echo '<input type="number" name="herringbone_design_cost" value="' . get_option('blockpaving_herringbone_design_cost') . '"/>';
       echo '<br/><label>Basketweave Design Cost per sqm</label>';
       echo '<input type="number" name="basketweave_design_cost" value="' . get_option('blockpaving_basketweave_design_cost') . '"/>';

       echo '<br/><input type="submit" name="submit" value="Save Prices">';
       echo '</form>';
   }

   // Register API endpoint for dynamic pricing
   add_action('rest_api_init', function () {
       register_rest_route('driveway-calculator/v1', '/calculate-cost', array(
           'methods' => 'POST',
           'callback' => 'calculate_driveway_cost',
       ));
   });

   function calculate_driveway_cost($data) {
       // Fetch the user input (e.g., surface type, size, etc.)
    $surface_type = $data['surface_type'];
    $area = $data['area'];
    $design = isset($data['design']) ? $data['design'] : null;

    // Fetch dynamic material and labor costs based on surface type
    $material_cost = get_option('driveway_' . $surface_type . '_material_cost');
    $labor_cost = get_option('driveway_' . $surface_type . '_labor_cost');

    // Base cost
    $total_cost = ($material_cost * $area) + ($labor_cost * $area);

    // For block paving, add additional design cost if applicable
    if ($surface_type === 'blockpaving' && $design) {
        $design_cost = get_option('blockpaving_' . $design . '_design_cost');
        $total_cost += ($design_cost * $area);
    }

    return new WP_REST_Response(array('total_cost' => $total_cost), 200);
   }

   function driveway_calculator_form() {
    ob_start();
    ?>
    <form id="driveway-calculator-form">
        <label for="surface">Surface Type</label>
        <select id="surface" name="surface">
            <option value="asphalt">Asphalt</option>
            <option value="concrete">Concrete</option>
            <option value="gravel">Gravel</option>
            <option value="blockpaving">Block Paving</option>
        </select>
        
        <div id="blockpaving-design" style="display: none;">
            <label for="design">Block Paving Design</label>
            <select id="design" name="design">
                <option value="herringbone">Herringbone</option>
                <option value="basketweave">Basketweave</option>
            </select>
        </div>

        <label for="area">Driveway Size (sq ft)</label>
        <input type="number" id="area" name="area">

        <button type="button" id="calculate-button">Calculate Cost</button>

        <div id="cost-display"></div>
    </form>

    <script>
    document.getElementById('surface').addEventListener('change', function() {
        if (this.value === 'blockpaving') {
            document.getElementById('blockpaving-design').style.display = 'block';
        } else {
            document.getElementById('blockpaving-design').style.display = 'none';
        }
    });

    document.getElementById('calculate-button').addEventListener('click', function() {
        const surface = document.getElementById('surface').value;
        const area = document.getElementById('area').value;
        const design = document.getElementById('design').value;

        fetch('/wp-json/driveway-calculator/v1/calculate-cost', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                surface_type: surface,
                area: area,
                design: design
            })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('cost-display').innerHTML = 'Total Cost: £' + data.total_cost;
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('driveway_calculator', 'driveway_calculator_form');
