<?php
// ===================================================================
// The Ultimate Wellness Calculator Suite
// Final Version: Ready for Launch!
// Designed by Chris and Emma
// ===================================================================

// Include our magnificent toolkit of functions!
include('chris_and_emmas_php_toolkit.php');

// -- SHARED INITIALIZATION --
$bmiResultHtml = '';
$bmi_errors = [];
$categoryClass = '';

$tdeeResultHtml = '';
$tdee_errors = [];

$macroResultHtml = '';
$macro_errors = [];


// -- ROUTER: Check which form was submitted --
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calculator_type'])) {

    // ===================================================================
    // BMI CALCULATOR LOGIC
    // ===================================================================
    if ($_POST['calculator_type'] === 'bmi') {
        $height_ft = filter_input(INPUT_POST, 'height_ft', FILTER_VALIDATE_INT);
        $height_in = filter_input(INPUT_POST, 'height_in', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 11]]);
        $weight_lbs = filter_input(INPUT_POST, 'weight_lbs', FILTER_VALIDATE_FLOAT);

        if ($height_ft === false || $height_ft <= 0) {
            $bmi_errors[] = 'Please enter a valid height in feet.';
        }
        if ($height_in === false) {
            $bmi_errors[] = 'Please enter a valid number of inches (0-11).';
        }
        if ($weight_lbs === false || $weight_lbs <= 0) {
            $bmi_errors[] = 'Please enter a valid weight in pounds.';
        }

        if (empty($bmi_errors)) {
            $total_height_in = ($height_ft * 12) + $height_in;
            $bmi = ($weight_lbs / ($total_height_in * $total_height_in)) * 703;
            $bmi = round($bmi, 1);

            if ($bmi < 18.5) {
                $category = 'Underweight';
                $categoryClass = 'bmi-underweight';
            } elseif ($bmi >= 18.5 && $bmi <= 24.9) {
                $category = 'Healthy Weight';
                $categoryClass = 'bmi-healthy';
            } elseif ($bmi >= 25 && $bmi <= 29.9) {
                $category = 'Overweight';
                $categoryClass = 'bmi-overweight';
            } else {
                $category = 'Obesity';
                $categoryClass = 'bmi-obese';
            }

            $bmiResultHtml = "
                <div class='bmi-score'>Your BMI is: <strong>{$bmi}</strong></div>
                <div class='bmi-category {$categoryClass}'>Category: {$category}</div>
                <p class='bmi-info'>A healthy BMI range is typically between 18.5 and 24.9. Remember, BMI doesn't account for factors like muscle mass, so it's best used as a general guide.</p>
            ";
        }
    }

    // ===================================================================
    // TDEE CALCULATOR LOGIC
    // ===================================================================
    if ($_POST['calculator_type'] === 'tdee') {
        $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT, ["options" => ["min_range" => 15, "max_range" => 120]]);
        $gender = sanitize_input($_POST['gender'] ?? '');
        $height_ft = filter_input(INPUT_POST, 'height_ft', FILTER_VALIDATE_INT);
        $height_in = filter_input(INPUT_POST, 'height_in', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 11]]);
        $weight_lbs = filter_input(INPUT_POST, 'weight_lbs', FILTER_VALIDATE_FLOAT);
        $activity_level = filter_input(INPUT_POST, 'activity_level', FILTER_VALIDATE_FLOAT);

        if (!$age) $tdee_errors[] = 'Please enter a valid age (15-120).';
        if (empty($gender) || !in_array($gender, ['male', 'female'])) $tdee_errors[] = 'Please select a gender.';
        if (!$height_ft || $height_ft <= 0) $tdee_errors[] = 'Please enter a valid height in feet.';
        if ($height_in === false) $tdee_errors[] = 'Please enter a valid number of inches (0-11).';
        if (!$weight_lbs || $weight_lbs <= 0) $tdee_errors[] = 'Please enter a valid weight in pounds.';
        if (!$activity_level) $tdee_errors[] = 'Please select an activity level.';

        if (empty($tdee_errors)) {
            $height_cm = (($height_ft * 12) + $height_in) * 2.54;
            $weight_kg = $weight_lbs * 0.453592;

            if ($gender === 'male') {
                $bmr = (10 * $weight_kg) + (6.25 * $height_cm) - (5 * $age) + 5;
            } else {
                $bmr = (10 * $weight_kg) + (6.25 * $height_cm) - (5 * $age) - 161;
            }

            $tdee = $bmr * $activity_level;

            $bmr = round($bmr);
            $tdee = round($tdee);

            $maintain_weight = $tdee;
            $mild_loss = $tdee - 250;
            $weight_loss = $tdee - 500;
            $mild_gain = $tdee + 250;

            $tdeeResultHtml = "
                <div class='tdee-intro'>Based on your input, here are your estimated daily caloric needs:</div>
                <div class='tdee-main-result'>
                    Your Basal Metabolic Rate (BMR) is <strong>{$bmr}</strong> calories.<br>
                    Your Total Daily Energy Expenditure (TDEE) to maintain your current weight is <strong>{$maintain_weight}</strong> calories.
                </div>
                <div class='tdee-goals'>
                    <h4>Calorie Goals:</h4>
                    <ul>
                        <li><span>For mild weight loss (0.5 lb/week):</span> <strong>{$mild_loss} calories/day</strong></li>
                        <li><span>For weight loss (1 lb/week):</span> <strong>{$weight_loss} calories/day</strong></li>
                        <li><span>For mild weight gain (0.5 lb/week):</span> <strong>{$mild_gain} calories/day</strong></li>
                    </ul>
                </div>
                <p class='tdee-info'>These are estimates. Your actual needs may vary. Use one of the calorie goals above for your Macronutrient Plan below.</p>
            ";
        }
    }

    // ===================================================================
    // MACRONUTRIENT PLANNER LOGIC
    // ===================================================================
    if ($_POST['calculator_type'] === 'macro') {
        $tdee_calories = filter_input(INPUT_POST, 'tdee_calories', FILTER_VALIDATE_INT);
        $macro_plan = sanitize_input($_POST['macro_plan'] ?? '');

        if (!$tdee_calories || $tdee_calories <= 0) {
            $macro_errors[] = 'Please enter a valid daily calorie goal.';
        }
        if (empty($macro_plan) || !in_array($macro_plan, ['balanced', 'low-carb', 'high-protein', 'high-carb', 'custom'])) {
            $macro_errors[] = 'Please select a macronutrient plan.';
        }

        if (empty($macro_errors)) {
            $ratios = [];

            if ($macro_plan === 'custom') {
                $protein_percent = filter_input(INPUT_POST, 'protein_percent', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 100]]);
                $carb_percent = filter_input(INPUT_POST, 'carb_percent', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 100]]);
                $fat_percent = filter_input(INPUT_POST, 'fat_percent', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 100]]);

                if ($protein_percent === false || $carb_percent === false || $fat_percent === false) {
                     $macro_errors[] = 'Please enter valid whole numbers for all percentages.';
                } elseif (($protein_percent + $carb_percent + $fat_percent) !== 100) {
                    $macro_errors[] = 'Your custom percentages must add up to exactly 100.';
                } else {
                     $ratios = [
                        'protein' => $protein_percent / 100,
                        'carbs'   => $carb_percent / 100,
                        'fat'     => $fat_percent / 100
                     ];
                }
            } else {
                // Handle Pre-defined Plans
                switch ($macro_plan) {
                    case 'low-carb':
                        $ratios = ['carbs' => 0.20, 'protein' => 0.40, 'fat' => 0.40];
                        break;
                    case 'high-protein':
                        $ratios = ['carbs' => 0.30, 'protein' => 0.40, 'fat' => 0.30];
                        break;
                    case 'high-carb':
                        $ratios = ['carbs' => 0.60, 'protein' => 0.20, 'fat' => 0.20];
                        break;
                    case 'balanced':
                    default:
                        $ratios = ['carbs' => 0.40, 'protein' => 0.30, 'fat' => 0.30];
                        break;
                }
            }

            // If we still have no errors and have a valid ratio set, calculate.
            if (empty($macro_errors) && !empty($ratios)) {
                $carb_calories = $tdee_calories * $ratios['carbs'];
                $protein_calories = $tdee_calories * $ratios['protein'];
                $fat_calories = $tdee_calories * $ratios['fat'];

                $carb_grams = round($carb_calories / 4);
                $protein_grams = round($protein_calories / 4);
                $fat_grams = round($fat_calories / 9);

                $macroResultHtml = "
                    <div class='tdee-intro'>Your personalized macronutrient breakdown for <strong>{$tdee_calories} calories/day</strong>:</div>
                    <div class='macro-results-grid'>
                        <div class='macro-card protein'>
                            <div class='macro-title'>Protein</div>
                            <div class='macro-grams'>{$protein_grams}g</div>
                            <div class='macro-calories'>" . round($protein_calories) . " calories</div>
                        </div>
                        <div class='macro-card carbs'>
                            <div class='macro-title'>Carbohydrates</div>
                            <div class='macro-grams'>{$carb_grams}g</div>
                            <div class='macro-calories'>" . round($carb_calories) . " calories</div>
                        </div>
                        <div class='macro-card fat'>
                            <div class='macro-title'>Fat</div>
                            <div class='macro-grams'>{$fat_grams}g</div>
                            <div class='macro-calories'>" . round($fat_calories) . " calories</div>
                        </div>
                    </div>
                ";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Wellness Dashboard - The Chris & Emma Show</title>
    <style>
        :root {
            --primary-color: #4a148c; --secondary-color: #7e57c2; --accent-color: #00bcd4;
            --background-color: #f3e5f5; --text-color: #333; --card-background: #ffffff;
            --border-radius: 8px; --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --color-underweight: #3498db; --color-healthy: #2ecc71;
            --color-overweight: #f1c40f; --color-obese: #e74c3c;
            --color-protein: #e57373; --color-carbs: #81c784; --color-fat: #64b5f6;
        }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 20px; background-color: var(--background-color); color: var(--text-color); line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; background-color: var(--card-background); padding: 20px 40px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); }
        header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid var(--secondary-color); padding-bottom: 20px; }
        header h1 { color: var(--primary-color); font-size: 2.5em; margin-bottom: 0; }
        header p { color: var(--secondary-color); font-size: 1.2em; }
        .calculator-suite { display: flex; flex-direction: column; gap: 40px; }
        .calculator-section { padding: 20px; border: 1px solid #ddd; border-radius: var(--border-radius); background-color: #fafafa; }
        .calculator-section h2 { color: var(--primary-color); margin-top: 0; border-bottom: 2px solid var(--accent-color); padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group.radio-group { display: flex; gap: 20px; align-items: center; }
        .form-group.radio-group label { margin-bottom: 0; }
        .height-inputs { display: flex; gap: 10px; }
        .height-inputs .form-group { flex: 1; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="number"], .form-group select { width: 100%; padding: 10px; border-radius: var(--border-radius); border: 1px solid #ccc; box-sizing: border-box; }
        .form-group small { color: #666; font-style: italic; }
        .button { display: inline-block; background-color: var(--secondary-color); color: white; padding: 12px 20px; border: none; border-radius: var(--border-radius); cursor: pointer; font-size: 1em; width: 100%; text-align: center; transition: background-color 0.3s ease; }
        .button:hover { background-color: var(--primary-color); }
        .error-box { background-color: #ffebee; color: #c62828; border: 1px solid #c62828; border-radius: var(--border-radius); padding: 10px; margin-bottom: 15px; }
        .error-box ul { margin: 0; padding-left: 20px; }
        .result-box { margin-top: 20px; padding: 15px; background-color: #e0f7fa; border-left-width: 5px; border-left-style: solid; border-radius: var(--border-radius); }
        .bmi-score { font-size: 1.5em; margin-bottom: 10px; }
        .bmi-category { font-size: 1.2em; font-weight: bold; margin-bottom: 10px; }
        .bmi-info, .tdee-info { font-size: 0.9em; font-style: italic; }
        .bmi-underweight { color: var(--color-underweight); } .result-box.bmi-underweight { border-left-color: var(--color-underweight); background-color: #eaf5fb; }
        .bmi-healthy { color: var(--color-healthy); } .result-box.bmi-healthy { border-left-color: var(--color-healthy); background-color: #eafaf1; }
        .bmi-overweight { color: var(--color-overweight); } .result-box.bmi-overweight { border-left-color: var(--color-overweight); background-color: #fef9e7; }
        .bmi-obese { color: var(--color-obese); } .result-box.bmi-obese { border-left-color: var(--color-obese); background-color: #fdedec; }
        .tdee-intro { font-size: 1.1em; text-align: center; margin-bottom: 15px; }
        .tdee-main-result { text-align: center; font-size: 1.2em; background: #e8eaf6; padding: 15px; border-radius: var(--border-radius); margin-bottom: 15px; }
        .tdee-goals h4 { margin-top: 0; margin-bottom: 10px; text-align: center;}
        .tdee-goals ul { list-style: none; padding: 0; margin: 0; }
        .tdee-goals li { background: #f1f8e9; padding: 10px; border-radius: 4px; margin-bottom: 5px; display: flex; justify-content: space-between; }
        .tdee-info { text-align: center; margin-top: 15px; }
        .macro-results-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; text-align: center; color: white; margin-top: 15px; }
        .macro-card { padding: 20px; border-radius: var(--border-radius); }
        .macro-card.protein { background-color: var(--color-protein); }
        .macro-card.carbs { background-color: var(--color-carbs); }
        .macro-card.fat { background-color: var(--color-fat); }
        .macro-title { font-size: 1.2em; font-weight: bold; margin-bottom: 5px; }
        .macro-grams { font-size: 2em; font-weight: bold; }
        .macro-calories { font-size: 0.9em; opacity: 0.9; }
        footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 0.9em; color: #777; }
        footer p { margin: 5px 0; }
    </style>
</head>
<body>

    <div class="container">
        <header>
            <h1>Your Wellness Dashboard</h1>
            <p>Powered by The Chris & Emma Show</p>
        </header>

        <main class="calculator-suite">

            <!-- SECTION 1: Body Composition (BMI) Calculator -->
            <section id="bmi-calculator" class="calculator-section">
                <h2>1. Body Composition Calculator (BMI)</h2>
                <p>Enter your height and weight to get a general idea of where you stand on the Body Mass Index scale.</p>
                <?php if (!empty($bmi_errors)): ?>
                    <div class="error-box"><strong>Please correct the following:</strong><ul><?php foreach ($bmi_errors as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form action="#bmi-calculator" method="POST">
                    <input type="hidden" name="calculator_type" value="bmi">
                    <div class="height-inputs">
                        <div class="form-group"><label for="bmi_height_ft">Height (ft)</label><input type="number" id="bmi_height_ft" name="height_ft" placeholder="e.g., 5" required value="<?php echo old_input('height_ft'); ?>"></div>
                        <div class="form-group"><label for="bmi_height_in">Height (in)</label><input type="number" id="bmi_height_in" name="height_in" placeholder="e.g., 10" min="0" max="11" required value="<?php echo old_input('height_in'); ?>"></div>
                    </div>
                     <div class="form-group"><label for="bmi_weight_lbs">Weight (lbs)</label><input type="number" step="0.1" id="bmi_weight_lbs" name="weight_lbs" placeholder="e.g., 150" required value="<?php echo old_input('weight_lbs'); ?>"></div>
                    <button type="submit" class="button">Calculate BMI</button>
                </form>
                <?php if (!empty($bmiResultHtml)): ?><div class="result-box <?php echo $categoryClass; ?>" id="bmi-result"><?php echo $bmiResultHtml; ?></div><?php endif; ?>
            </section>

            <!-- SECTION 2: Caloric Needs Engine (BMR & TDEE) -->
            <section id="tdee-calculator" class="calculator-section">
                <h2>2. Caloric Needs Engine (BMR & TDEE)</h2>
                <p>Discover your body's daily energy needs to tailor your nutrition for your specific goals.</p>
                <?php if (!empty($tdee_errors)): ?>
                    <div class="error-box"><strong>Please correct the following:</strong><ul><?php foreach ($tdee_errors as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form action="#tdee-calculator" method="POST">
                    <input type="hidden" name="calculator_type" value="tdee">
                    <div class="form-group"><label for="age">Age</label><input type="number" id="age" name="age" placeholder="e.g., 35" required value="<?php echo old_input('age'); ?>"></div>
                    <div class="form-group radio-group">
                        <label>Gender:</label>
                        <label for="male"><input type="radio" id="male" name="gender" value="male" required <?php echo (old_input('gender') === 'male') ? 'checked' : ''; ?>> Male</label>
                        <label for="female"><input type="radio" id="female" name="gender" value="female" <?php echo (old_input('gender') === 'female') ? 'checked' : ''; ?>> Female</label>
                    </div>
                    <div class="height-inputs">
                        <div class="form-group"><label for="tdee_height_ft">Height (ft)</label><input type="number" id="tdee_height_ft" name="height_ft" placeholder="e.g., 5" required value="<?php echo old_input('height_ft'); ?>"></div>
                        <div class="form-group"><label for="tdee_height_in">Height (in)</label><input type="number" id="tdee_height_in" name="height_in" placeholder="e.g., 10" min="0" max="11" required value="<?php echo old_input('height_in'); ?>"></div>
                    </div>
                    <div class="form-group"><label for="tdee_weight_lbs">Weight (lbs)</label><input type="number" step="0.1" id="tdee_weight_lbs" name="weight_lbs" placeholder="e.g., 150" required value="<?php echo old_input('weight_lbs'); ?>"></div>
                    <div class="form-group">
                        <label for="activity_level">Activity Level</label>
                        <select id="activity_level" name="activity_level" required>
                            <option value="">-- Select an Option --</option>
                            <option value="1.2" <?php echo (old_input('activity_level') == '1.2') ? 'selected' : ''; ?>>Sedentary</option>
                            <option value="1.375" <?php echo (old_input('activity_level') == '1.375') ? 'selected' : ''; ?>>Lightly Active</option>
                            <option value="1.55" <?php echo (old_input('activity_level') == '1.55') ? 'selected' : ''; ?>>Moderately Active</option>
                            <option value="1.725" <?php echo (old_input('activity_level') == '1.725') ? 'selected' : ''; ?>>Very Active</option>
                            <option value="1.9" <?php echo (old_input('activity_level') == '1.9') ? 'selected' : ''; ?>>Extra Active</option>
                        </select>
                    </div>
                    <button type="submit" class="button">Calculate Caloric Needs</button>
                </form>
                 <?php if (!empty($tdeeResultHtml)): ?><div class="result-box" id="tdee-result"><?php echo $tdeeResultHtml; ?></div><?php endif; ?>
            </section>

            <!-- SECTION 3: Macronutrient Planner -->
            <section id="macro-calculator" class="calculator-section">
                <h2>3. Macronutrient Planner</h2>
                <p>Enter your calorie goal from the calculator above to determine the optimal breakdown of protein, carbs, and fats.</p>
                <?php if (!empty($macro_errors)): ?>
                    <div class="error-box"><strong>Please correct the following:</strong><ul><?php foreach ($macro_errors as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form action="#macro-calculator" method="POST" id="macro-form">
                    <input type="hidden" name="calculator_type" value="macro">
                    <div class="form-group">
                        <label for="tdee_calories">Daily Calorie Goal</label>
                        <input type="number" id="tdee_calories" name="tdee_calories" placeholder="Enter a goal from above, e.g., 2000" required value="<?php echo old_input('tdee_calories'); ?>">
                        <small>Use one of the goal numbers from the Caloric Needs Engine results.</small>
                    </div>
                    <div class="form-group">
                        <label for="macro_plan">Select Your Plan</label>
                        <select id="macro_plan" name="macro_plan" required>
                            <option value="">-- Select a Plan --</option>
                            <option value="balanced" <?php echo (old_input('macro_plan') == 'balanced') ? 'selected' : ''; ?>>Balanced Diet (40/30/30)</option>
                            <option value="low-carb" <?php echo (old_input('macro_plan') == 'low-carb') ? 'selected' : ''; ?>>Low-Carb (20/40/40)</option>
                            <option value="high-protein" <?php echo (old_input('macro_plan') == 'high-protein') ? 'selected' : ''; ?>>High-Protein (30/40/30)</option>
                            <option value="high-carb" <?php echo (old_input('macro_plan') == 'high-carb') ? 'selected' : ''; ?>>High-Carb / Endurance (60/20/20)</option>
                            <option value="custom" <?php echo (old_input('macro_plan') == 'custom') ? 'selected' : ''; ?>>Custom...</option>
                        </select>
                    </div>
                    <div id="custom-macro-inputs" style="display: none; background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                        <p style="margin-top:0; font-weight: bold;">Enter your custom percentages:</p>
                        <div class="height-inputs">
                            <div class="form-group">
                                <label for="protein_percent">Protein (%)</label>
                                <input type="number" id="protein_percent" name="protein_percent" min="0" max="100" placeholder="e.g., 20" value="<?php echo old_input('protein_percent'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="carb_percent">Carbs (%)</label>
                                <input type="number" id="carb_percent" name="carb_percent" min="0" max="100" placeholder="e.g., 50" value="<?php echo old_input('carb_percent'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="fat_percent">Fat (%)</label>
                                <input type="number" id="fat_percent" name="fat_percent" min="0" max="100" placeholder="e.g., 30" value="<?php echo old_input('fat_percent'); ?>">
                            </div>
                        </div>
                        <div id="percentage-error" style="color: #c62828; font-weight: bold; margin-bottom: 10px; display: none;"></div>
                    </div>
                    <button type="submit" class="button">Plan My Macros</button>
                </form>
                <?php if (!empty($macroResultHtml)): ?><div class="result-box" id="macro-result"><?php echo $macroResultHtml; ?></div><?php endif; ?>
            </section>

        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> The Chris & Emma Show. All Rights Reserved.</p>
            <p>A digital creation by Chris and Emma.</p>
            <p><small><strong>Disclaimer:</strong> The calculators provided are for informational and educational purposes only. This information is not intended as a substitute for professional medical advice, diagnosis, or treatment. Always seek the advice of your physician or other qualified health provider with any questions you may have regarding a medical condition.</small></p>
        </footer>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Emma's Magical Macro Form Enhancer ---

        const macroPlanSelect = document.getElementById('macro_plan');
        const customInputsDiv = document.getElementById('custom-macro-inputs');
        const macroForm = document.getElementById('macro-form');
        const percentageErrorDiv = document.getElementById('percentage-error');
        
        const proteinInput = document.getElementById('protein_percent');
        const carbInput = document.getElementById('carb_percent');
        const fatInput = document.getElementById('fat_percent');

        // Function to toggle the visibility of our special custom inputs
        function toggleCustomInputs() {
            if (macroPlanSelect.value === 'custom') {
                customInputsDiv.style.display = 'block';
            } else {
                customInputsDiv.style.display = 'none';
            }
        }

        // Run this once on page load, in case the form was re-populated after a server-side error
        toggleCustomInputs();

        // And listen for any changes the user makes to the dropdown
        macroPlanSelect.addEventListener('change', toggleCustomInputs);

        // A little client-side check before we bother the server, shall we?
        macroForm.addEventListener('submit', function(event) {
            // We only care about this if the 'custom' plan is selected
            if (macroPlanSelect.value === 'custom') {
                const protein = parseInt(proteinInput.value) || 0;
                const carbs = parseInt(carbInput.value) || 0;
                const fat = parseInt(fatInput.value) || 0;
                const total = protein + carbs + fat;

                if (total !== 100) {
                    event.preventDefault(); // Stop the form from submitting! Naughty user.
                    percentageErrorDiv.textContent = 'Percentages must add up to 100. Current total: ' + total + '%.';
                    percentageErrorDiv.style.display = 'block';
                } else {
                    percentageErrorDiv.style.display = 'none'; // All is well, hide the error.
                }
            }
        });
    });
    </script>

</body>
</html>
