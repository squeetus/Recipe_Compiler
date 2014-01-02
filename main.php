<?php
$Units = 'cup cups can g gram grams kg kilogram L l liter lb pound mL ml milliliter oz ounce pt pint t tsp teaspoon T TB Tbl Tbsp tablespoon head inches inch batch clove bunch ';

$IngredientList = array();

// Class structure for an ingredient
class Ingredient {
    public $amount = 0;
    public $cost = 0.0;
    public $item = "";
    public $measure = "";
    
    public function display() {
        echo $this->amount . "\t\t" . $this->measure . "\t\t" . $this->item . "\t\t" . $this->cost . "\n";
    }
}

// Finds ingredients from the given string
function parseForIngredients($content) {
    $sentinel = true;
    $count = 0; 
    global $Units;
    global $IngredientList;
    
    while($sentinel) {
        
        $ingredient = new Ingredient();
        
        $startpos = strpos($content, '<li class="ingredient" itemprop="ingredients">');
        $startpos += 46;
        $content = substr($content, $startpos);
        $endpos = strpos($content, '</li>');
        $line = substr($content, 0, $endpos);
        
        $line = explode(" ", $line);
        
        if(is_numeric($line[0]))
            $ingredient->amount = (int) $line[0];
         
        $i = 0;
        foreach($line as &$value) {
            $i++;
        }
        
        $cost = substr($line[$i-1],1);
        if(is_numeric($cost))
            $ingredient->cost = (double) $cost;
        
        $item = "";
        
        for($j=1;$j<$i-1;$j++)
            if(strpos($Units, $line[$j]) !== false) {
                $ingredient->measure = $line[$j];
            } else {
                $item = $item . " " . $line[$j];    
            }
            
        $ingredient->item = $item;
           
        $IngredientList[$count] = &$ingredient;
        if(strpos($content,'<li class="ingredient" itemprop="ingredients">') === false)
            $sentinel = false;
        
        echo "<br />";
        $count += 1;
    }
}

// Prints out contents of ingredient array
function printIngredients() {
    global $IngredientList;    

    echo "Amount\t\tMeasurement\t\tItem\t\tCost\n\n";

    foreach($IngredientList as &$ingredient) {
        $ingredient->display();
    }

}

// Point of entry
if ( ! empty($_POST['Recipe_Input_Box'])){
    
    $url = $_POST['Recipe_Input_Box'];
    
    echo "
    <form name='form' method='post' action='main.php'>
    recipe (url): <INPUT TYPE='Text' VALUE='" . htmlspecialchars($url) . "' NAME='Recipe_Input_Box'>
    <input type='submit' name='submit' value='Submit'></form>
    ";
    
    $url = $_POST['Recipe_Input_Box'];
    $content = file_get_contents($url);
    
    parseForIngredients($content);
    
    //printIngredients();
    var_dump ($IngredientList);
} else {
    echo "
    <form name='form' method='post' action='main.php'>
    recipe (url): <INPUT TYPE='Text' VALUE='' NAME='Recipe_Input_Box'>
    <input type='submit' name='submit' value='Submit'></form>
    ";
    
}


?>

