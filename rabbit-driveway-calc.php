<?php
   /*
   Plugin Name: Driveway Cost Calculator
   Description: A custom plugin to manage driveway pricing and calculations.
   Version: 9.5.0
   Author: Your Name
   */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
define('WPDC_PLUGIN_URL', plugin_dir_url(__FILE__));
   // Register styles and scripts
function wpdc_enqueue_assets() {
    wp_enqueue_style('dc-style', WPDC_PLUGIN_URL . 'assets/css/dc-style.css');
}

add_action('wp_enqueue_scripts', 'wpdc_enqueue_assets');

function dc_admin_enqueue_styles() {
    wp_enqueue_style('dc-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css');
}
add_action('admin_enqueue_scripts', 'dc_admin_enqueue_styles');

add_action('init', function () {
  register_post_type('driveway_quote', [
    'label' => 'Driveway Quotes',
    'public' => true,
    'show_ui' => true,
      'show_in_rest' => true, // <--- Important for Zapier!!
    'rest_base' => 'driveway-quotes', // URL like /wp-json/wp/v2/driveway-quotes
    'rest_controller_class' => 'WP_REST_Posts_Controller',
    'supports' => ['title', 'editor', 'custom-fields'],
    'menu_icon' => 'dashicons-email-alt',
  ]);
});



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

    
    echo '<div class="dcs-wrap">';
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

       $default_template = "Hi {name},\n\nThank you for using our driveway cost calculator.\n\n".
                    "Surface: {surface}\nDesign: {design}\nArea: {area} m²\n".
                    "Estimated Cost: £{cost}\n\nBest regards,\nYour Company";

$current_template = get_option('driveway_email_template', $default_template);

if (isset($_POST['submit'])) {
  update_option('driveway_email_template', wp_kses_post($_POST['email_template']));
  $current_template = $_POST['email_template'];
}

echo '<h2>Email Template</h2>';
echo '<p>You can use the following placeholders: <code>{name}</code>, <code>{surface}</code>, <code>{design}</code>, <code>{area}</code>, <code>{cost}</code>, <code>{site_name}</code>, <code>{admin_email}</code>, <code>{site_url}</code> </p>';
echo '<textarea name="email_template" rows="10" cols="80" style="width:100%;">' . esc_textarea($current_template) . '</textarea>';

$from_email = get_option('driveway_from_email', get_option('admin_email'));

if (isset($_POST['submit'])) {
  update_option('driveway_from_email', sanitize_email($_POST['from_email']));
  $from_email = $_POST['from_email'];
}

echo '<h2>Email Sender Settings</h2>';
echo '<label for="from_email">From Email Address</label><br>';
echo '<input type="email" name="from_email" id="from_email" value="' . esc_attr($from_email) . '" style="width:100%;">';
echo '<p style="margin-bottom:10px; width:100%;">Note: This email address will be used as the sender for the estimate emails.</p>';
       echo '<div class="dcs-form-group">';
       echo '<h2>Asphalt Pricing</h2>';
       echo '<label>Material Cost per sqm</label>';
       echo '<input type="number" name="asphalt_material_cost" value="' . get_option('driveway_asphalt_material_cost') . '"/>';
       echo '<br/><label>Labor Cost per sqm</label>';
       echo '<input type="number" name="asphalt_labor_cost" value="' . get_option('driveway_asphalt_labor_cost') . '"/>';
        echo '</div>';
        echo '<div class="dcs-form-group">';
       echo '<h2>Concrete Pricing</h2>';
       echo '<label>Material Cost per sqm</label>';
       echo '<input type="number" name="concrete_material_cost" value="' . get_option('driveway_concrete_material_cost') . '"/>';
       echo '<br/><label>Labor Cost per sqm</label>';
       echo '<input type="number" name="concrete_labor_cost" value="' . get_option('driveway_concrete_labor_cost') . '"/>';
        echo '</div>';
        echo '<div class="dcs-form-group">';
       echo '<h2>Gravel Pricing</h2>';
       echo '<label>Material Cost per sqm</label>';
       echo '<input type="number" name="gravel_material_cost" value="' . get_option('driveway_gravel_material_cost') . '"/>';
       echo '<br/><label>Labor Cost per sqm</label>';
       echo '<input type="number" name="gravel_labor_cost" value="' . get_option('driveway_gravel_labor_cost') . '"/>';
        echo '</div>';
        echo '<div class="dcs-form-group">';
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
        echo '</div>';
       echo '<br/><input class="button" type="submit" name="submit" value="Save Prices">';
       echo '</form>';
        echo '</div>';
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
    $name = sanitize_text_field($data['name'] ?? '');

     $email = sanitize_email($data['email'] ?? '');

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

    $email_template = get_option('driveway_email_template');

$replacements = [
  '{name}' => $name ?: 'Customer',
  '{surface}' => ucfirst($surface_type),
  '{design}' => $design ?: 'N/A',
  '{area}' => $area,
  '{cost}' => number_format($total_cost, 2),
   '{site_name}' => get_bloginfo('name'),
    '{site_url}' => get_site_url(),
  '{admin_email}' => get_option('admin_email'),
];

$email_message = str_replace(array_keys($replacements), array_values($replacements), $email_template);


    // Optional: Email the quote
  if ($email && is_email($email)) {
    // $subject = "Your Driveway Cost Estimate";
    // $greeting = $name ? "Hi $name," : "Hi,";
    // $message = "$greeting\n\nThank you for using our driveway calculator.\n\n".
    //            "Surface: $surface_type\n".
    //            ($design ? "Design: $design\n" : "").
    //            "Area: $area m²\n".
    //            "Estimated Cost: £" . number_format($total_cost, 2) . "\n\n".
    //            "Best regards,\nAndrew York Landscaping";

    // wp_mail($email, $subject, $message);
    // wp_mail($email, "Your Driveway Estimate", $email_message);

     // Build content
  $quote_title = "Quote from " . ($name ?: 'User') . " (" . date('Y-m-d H:i') . ")";
  $quote_content = "Driveway quote submitted via calculator.";

  // Create the post
  $post_id = wp_insert_post([
    'post_type' => 'driveway_quote',
    'post_title' => $quote_title,
    'post_content' => $quote_content,
    'post_status' => 'publish',
  ]);

  // Structured post meta
  if ($post_id && !is_wp_error($post_id)) {
    update_post_meta($post_id, 'name', $name);
    update_post_meta($post_id, 'email', $email);
    update_post_meta($post_id, 'surface', $surface_type);
    update_post_meta($post_id, 'design', $design);
    update_post_meta($post_id, 'area', $area);
    update_post_meta($post_id, 'total_cost', number_format($total_cost, 2));
  }

    $from_email = get_option('driveway_from_email', get_option('admin_email'));
$site_name = get_bloginfo('name');

$headers = [
  "From: $site_name <$from_email>",
  "Reply-To: $from_email",
  "Content-Type: text/plain; charset=UTF-8"
];

wp_mail($email, "Your Driveway Estimate from $site_name", $email_message, $headers);

  }

    return new WP_REST_Response(array('total_cost' => $total_cost), 200);
   }

   function driveway_calculator_form() {
    ob_start();
    ?>
    <form id="drivewayCalculatorForm">
  <div class="step step-1">
    <h3>Step 1: Select Surface</h3>
    <select id="surfaceType" required>
      <option value="">--Choose Surface--</option>
      <option value="asphalt">Asphalt</option>
      <option value="concrete">Concrete</option>
      <option value="gravel">Gravel</option>
      <option value="blockpaving">Block Paving</option>
    </select>
    <button type="button" class="next">Next</button>
  </div>

  <div class="step step-2" style="display: none;">
    <h3>Step 2: Enter Driveway Area (sqm)</h3>
    <input type="number" id="areaInput" required min="1" placeholder="e.g. 50" />
    <button type="button" class="prev">Previous</button>
    <button type="button" class="next">Next</button>
  </div>

  <div class="step step-3" style="display: none;">
    <h3>Step 3: Choose Block Paving Design</h3>
    <select id="design" name="design">
      <option value="">--Choose Design--</option>
      <option value="herringbone">Herringbone</option>
      <option value="basketweave">Basketweave</option>
    </select>
    <button type="button" class="prev">Previous</button>
    <button type="button" class="next">Next</button>
  </div>

 <div class="step step-4" style="display: none;">
  <h3>Step 4: Contact Details</h3>
  <input type="text" id="nameInput" placeholder="Your Name" />
  <input type="email" id="emailInput" placeholder="you@example.com" />
  <button type="button" class="prev">Previous</button>
  <button type="submit">Submit</button>
</div>

  <!-- <div class="step step-4" style="display: none;">
  <h3>Step 4: Enter Your Email (optional)</h3>
  <input type="email" id="emailInput" placeholder="you@example.com" />
  <button type="button" class="prev">Previous</button>
  <button type="button" class="next">Next</button>
</div>

<div class="step step-5" style="display: none;">
  <h3>Step 5: Estimated Cost</h3>
  <div id="costOutput">Calculating...</div>
  <button type="button" class="prev">Previous</button>
</div> -->

  
</form>
<div id="confirmation" style="display:none; text-align: center;">
  <h3>Thanks! Here's your Estimate:</h3>
  <div id="costOutput"></div>
</div>
 <script>
  document.addEventListener("DOMContentLoaded", function () {
    const steps = document.querySelectorAll(".step");
    const form = document.getElementById("drivewayCalculatorForm");
    const confirmation = document.getElementById("confirmation");
    const costOutput = document.getElementById("costOutput");

    let currentStep = 0;

    const surfaceInput = document.getElementById("surfaceType");
    const areaInput = document.getElementById("areaInput");
    const designInput = document.getElementById("design");
    const emailInput = document.getElementById("emailInput");

    const showStep = (index) => {
      steps.forEach((step, i) => step.style.display = i === index ? "block" : "none");
    };

    const goToNext = () => {
      if (currentStep === 0 && !surfaceInput.value) return alert("Please select a surface type.");
      if (currentStep === 1 && !areaInput.value) return alert("Please enter the area.");

      // Skip Step 3 (design) if not blockpaving
      if (currentStep === 1 && surfaceInput.value !== "blockpaving") {
        currentStep += 2;
      } else {
        currentStep++;
      }

      showStep(currentStep);
    };

    const goToPrev = () => {
      if (currentStep === 3 && surfaceInput.value !== "blockpaving") {
        currentStep -= 2;
      } else {
        currentStep--;
      }
      showStep(currentStep);
    };

    document.querySelectorAll(".next").forEach(btn => btn.addEventListener("click", goToNext));
    document.querySelectorAll(".prev").forEach(btn => btn.addEventListener("click", goToPrev));

    form.addEventListener("submit", function (e) {
      e.preventDefault();
const name = document.getElementById("nameInput").value.trim();
      const payload = {
        surface_type: surfaceInput.value,
        area: parseFloat(areaInput.value),
        ...(surfaceInput.value === "blockpaving" && designInput.value ? { design: designInput.value } : {}),
        ...(name ? { name: name } : {}),
        ...(emailInput.value ? { email: emailInput.value } : {})
      };

      costOutput.innerText = "Calculating...";
      form.style.display = "none";
      confirmation.style.display = "block";

      fetch("/wp-json/driveway-calculator/v1/calculate-cost", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      })
        .then(res => res.json())
        .then(data => {
  if (data.total_cost) {
    const formatted = data.total_cost.toLocaleString("en-GB", {
      style: "currency",
      currency: "GBP",
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
    costOutput.innerText = `Estimated Total Cost: ${formatted}`;
  } else {
    costOutput.innerText = "Error: Couldn't calculate cost.";
  }
})
        .catch(err => {
          console.error("Cost calculation error:", err);
          costOutput.innerText = "Sorry, something went wrong.";
        });
    });

    showStep(currentStep);
  });
</script>



<!-- <style>
  form#drivewayCalculatorForm {
    max-width: 500px;
    margin: auto;
    font-family: sans-serif;
    background: #f8f8f8;
    padding: 20px;
    color:#000;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
  form#drivewayCalculatorForm .step {
    transition: all 0.3s ease-in-out;
  }
  form#drivewayCalculatorForm h3 {
    margin-bottom: 10px;
  }
  form#drivewayCalculatorForm select,
  form#drivewayCalculatorForm input {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 6px;
  }
  form#drivewayCalculatorForm button {
   padding:10px 20px;
  margin-right: 10px;
  background:#1d4ed8;
  color: #fff;
  border:none;
  border-radius:6px;
  cursor: pointer;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 1px;
  transition: background 0.3s;
  }
  form#drivewayCalculatorForm button:hover {
    background: #2563eb;
    transition: background 0.3s;
  }
</style> -->

    <!-- <form id="driveway-calculator-form">
        <label class="mb-3" for="surface">Surface Type</label>
        <select class="mb-3" id="surface" name="surface">
            <option value="asphalt">Asphalt</option>
            <option value="concrete">Concrete</option>
            <option value="gravel">Gravel</option>
            <option value="blockpaving">Block Paving</option>
        </select>
        
        <div id="blockpaving-design" style="display: none;">
            <label class="mb-3" for="design">Block Paving Design</label>
            <select class="mb-3" id="design" name="design">
                <option value="herringbone">Herringbone</option>
                <option value="basketweave">Basketweave</option>
            </select>
        </div>

        <label class="mb-3" for="area">Driveway Size (sq ft)</label>
        <input class="mb-3" type="number" id="area" name="area">

        <button class=" btn btn-outline-light mb-3" type="button" id="calculate-button">Calculate Cost</button>

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
    </script> -->
    <?php
    return ob_get_clean();
}
add_shortcode('driveway_calculator', 'driveway_calculator_form');

function dcwp_register_acf_blocks() {
    /**
     * We register our block's with WordPress's handy
     * register_block_type();
     *
     * @link https://developer.wordpress.org/reference/functions/register_block_type/
     */
    register_block_type( plugin_dir_path(__FILE__) . 'blocks/driveway-calc' );
}
// Here we call our tt3child_register_acf_block() function on init.
add_action( 'init', 'dcwp_register_acf_blocks' );

add_action('add_meta_boxes', function () {
  add_meta_box('quote_details_meta', 'Quote Details', 'render_quote_details_meta', 'driveway_quote', 'normal', 'high');
});

function render_quote_details_meta($post) {
  $fields = ['name', 'email', 'surface', 'design', 'area', 'total_cost'];
  echo '<table class="form-table">';
  foreach ($fields as $field) {
    $value = esc_html(get_post_meta($post->ID, $field, true));
    echo "<tr><th>$field</th><td><input type='text' value='$value' readonly class='regular-text' /></td></tr>";
  }
  echo '</table>';
}


add_action('rest_api_init', function () {
  $fields = ['name', 'email', 'surface', 'design', 'area', 'total_cost'];

  foreach ($fields as $field) {
    register_rest_field('driveway_quote', $field, [
      'get_callback' => function($object) use ($field) {
        return get_post_meta($object['id'], $field, true);
      },
      'update_callback' => null,
      'schema' => [
        'description' => ucfirst(str_replace('_', ' ', $field)),
        'type' => 'string',
      ],
    ]);
  }
});
