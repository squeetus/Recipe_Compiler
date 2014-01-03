<?php
$Units = "to taste as box needed cup cups can g gram grams kg kilogram L l liter lb pound mL ml milliliter oz. oz ounce pt pint t tsp teaspoon T TB Tbl Tbsp tablespoon head inches inch batch clove cloves bunch";
$Fractions = array(
    "&frac12;" => .5,
    "&frac14;" => .25,
    "&frac13;" => .333,
    "&frac34;" => .75
    );
$IngredientList = array();
$RecipeList = array();

// Class structure for an ingredient
class Ingredient {
    public $amount = 0;
    public $cost = 0.0;
    public $item = "";
    public $measure = "";
    
    public function display() {
        echo "<td>" . $this->amount . "</td><td>" . $this->measure . "</td><td>" . $this->item . "</td><td>$" . $this->cost . "</td>";
    }
}

// Checks to see if the value is a fraction
function is_fraction($x) {
    global $Fractions;
    foreach($Fractions as $key => $val) {
        //echo $x . " " . $key . " " . $val . "<br />";
        
        if($x == $key) {
            //echo $x . " " . $key . " " . $val . "<br />";
            return true;
        }
    }
    return false;
}

// Returns the decimal value for an html fraction
function get_fraction($x) {
    global $Fractions;
    foreach($Fractions as $key => $val) {
        if($x == $key)
            return $val;
    }
    return false;
}

// Finds ingredients from the given string
function parseForIngredients($content) {
    $sentinel = true;
    $count = 0; 
    global $Units;
    global $IngredientList;
    
    
    while($sentinel) {
        $item = "";
        $measure = "";
        $word = "";
        $ingredient = new Ingredient();
        
        $startpos = strpos($content, '<li class="ingredient" itemprop="ingredients">');
        if($startpos !== false)
            $startpos += 46;
        $content = substr($content, $startpos);
        $endpos = strpos($content, '</li>');
        $line = substr($content, 0, $endpos);
        
        $line = explode(" ", $line);
        
        $word = $line[0];
        
        if(is_numeric($word)) {
            $ingredient->amount = (int) $word;
        } else if(is_fraction($word)) {
            $ingredient->amount = (double) get_fraction($word);
        } else {
            $ingredient->amount = "-";
        }
        
        $i = count($line);
        
        $cost = substr($line[$i-1],1);
        
        if(is_numeric($cost))
            $ingredient->cost = (double) $cost;
        
        for($j=0;$j<$i-1;$j++) {
            $word = $line[$j];
            if(!(is_numeric($word) || is_fraction($word))) 
            {
                if((strpos($Units, $word) !== false) || (substr($word, 0,1) === '(') || (substr($word, -1) === ')')){
                    $measure = $measure . $word . " ";
                } else {
                    $item = $item . " " . $word; 
                }
            }
        }
        
        if(empty($measure))
            $ingredient->measure = "-";
        else
            $ingredient->measure = $measure;
            
        $ingredient->item = $item;
        
        $inList = false;
        
        foreach($IngredientList as $struct) {
            if($ingredient->item == $struct->item) {
                $inList = true;
                break;
            }
        }
          
        if(!$inList) 
            $IngredientList[] = $ingredient;
        
        if(strpos($content,'<li class="ingredient" itemprop="ingredients">') === false)
            $sentinel = false;
            
        $count += 1;
    }
}

// Prints out contents of ingredient array to file
function writeIngredientsToFile(){
    global $IngredientList;   
    $file = 'datafile.txt';
    $current = file_get_contents($file);

    foreach($IngredientList as $ingredient) {
        $current .= $ingredient->amount . " " . $ingredient->measure . " " . $ingredient->item . " " . $ingredient->cost . "\n";
    }
    
    file_put_contents($file, $current);
}




// Prints out contents of ingredient array
function printIngredients() {
    global $IngredientList;    

    echo "<table cellspacing='10'><tr align='right'><td>Amount</td><td>Measurement</td><td>Item</td><td>Cost</td></tr>";
    
    foreach($IngredientList as $ingredient) {
        echo "<tr>" . $ingredient->display() . "</tr>";
    }

    echo "</table>";
}

// Prints all the current recipes
function printRecipeList() {
    global $RecipeList;
    
    echo "<ul>";
    foreach($RecipeList as $recipe) {
        echo "<li><a href='" . $recipe . "'>" . $recipe . "</a></li>";
    }
    echo "</ul>";
    
}
    
function loadFromFile() {
    global $IngredientList;   
    $file = 'datafile.txt';
    $current = file_get_contents($file);

    
    
    file_put_contents($file, $current);
}

/************************************************************
 *                                                          *
 *                  Point of Entry                          *
 *                                                          *
 * **********************************************************/
loadFromFile();

echo "Shopping List<br /><br />";

if ( ! empty($_POST['Recipe_Input_Box'])){
    
    $url = $_POST['Recipe_Input_Box'];
    
    echo "
    <form name='form' method='post' action='main.php'>
    recipe (url): <INPUT TYPE='Text' VALUE='" . htmlspecialchars($url) . "' NAME='Recipe_Input_Box'>
    <input type='submit' name='submit' value='Submit'></form>
    ";
    
    $url = $_POST['Recipe_Input_Box'];
    $content = file_get_contents($url);
    
    $RecipeList[] = $url;
    printRecipeList();
    
    parseForIngredients($content);
    
    printIngredients();
    writeIngredientsToFile();
   
} else {
    echo "
    <form name='form' method='post' action='main.php'>
    recipe (url): <INPUT TYPE='Text' VALUE='' NAME='Recipe_Input_Box'>
    <input type='submit' name='submit' value='Submit'></form>
    ";
    
    printRecipeList();
}

?>
