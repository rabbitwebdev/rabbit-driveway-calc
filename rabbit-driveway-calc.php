<?php
   /*
   Plugin Name: Driveway Cost Calculator
   Description: A custom plugin to manage driveway pricing and calculations.
   Version: 7.5.1
   Author: Your Name
   */
define('WPDC_PLUGIN_URL', plugin_dir_url(__FILE__));
   // Register styles and scripts
function wpdc_enqueue_assets() {
    wp_enqueue_style('dc-style', WPDC_PLUGIN_URL . 'assets/css/dc-style.css');
}

add_action('wp_enqueue_scripts', 'wpdc_enqueue_assets');



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

    // Optional: Email the quote
  if ($email && is_email($email)) {
    $subject = "Your Driveway Cost Estimate";
    $greeting = $name ? "Hi $name," : "Hi,";
    $message = "$greeting\n\nThank you for using our driveway calculator.\n\n".
               "Surface: $surface_type\n".
               ($design ? "Design: $design\n" : "").
               "Area: $area m²\n".
               "Estimated Cost: £" . number_format($total_cost, 2) . "\n\n".
               "Best regards,\nAndrew York Landscaping";

    wp_mail($email, $subject, $message);
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



<style>
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
    padding: 10px 20px;
    margin-right: 10px;
    background: #1d4ed8;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
  }
  form#drivewayCalculatorForm button:hover {
    background: #2563eb;
  }
</style>

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
