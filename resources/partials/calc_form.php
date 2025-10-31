<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>HTML Calculator</title>
</head>
<body bgcolor= "#000000" text= "gold">
<!--
<form name="calculator" >
    <input type="button" value="1" onClick="document.calculator.ans.value+='1'">
    <input type="button" value="2" onClick="document.calculator.ans.value+='2'">
    <input type="button" value="3" onClick="document.calculator.ans.value+='3'"><br>
    <input type="button" value="4" onClick="document.calculator.ans.value+='4'">
    <input type="button" value="5" onClick="document.calculator.ans.value+='5'">
    <input type="button" value="6" onClick="document.calculator.ans.value+='6'">
    <input type="button" value="7" onClick="document.calculator.ans.value+='7'"><br>
    <input type="button" value="8" onClick="document.calculator.ans.value+='8'">
    <input type="button" value="9" onClick="document.calculator.ans.value+='9'">
    <input type="button" value="-" onClick="document.calculator.ans.value+='-'">
    <input type="button" value="+" onClick="document.calculator.ans.value+='+'"><br>
    <input type="button" value="*" onClick="document.calculator.ans.value+='*'">
    <input type="button" value="/" onClick="document.calculator.ans.value+='/'">

    <input type="button" value="0" onClick="document.calculator.ans.value+='0'">
    <input type="reset" value="Reset">
    <input type="button" value="=" onClick="document.calculator.ans.value=eval(document.calculator.ans.value)">
    <br>Solution is <input type="textfield" name="ans" value="">
</form>-->

<form method="post" action="">
    <input type="number" name="num1" placeholder="Число 1" required>
    <select name="operator" required>
        <option value="+">+</option>
        <option value="-">-</option>
        <option value="*">*</option>
        <option value="/">/</option>
    </select>
    <input type="number" name="num2" placeholder="Число 2" required>
    <button type="submit">Рассчитать</button>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $num1 = $_POST['num1'];
    $operator = $_POST['operator'];
    $num2 = $_POST['num2'];

    switch ($operator) {
        case '+':
            $result = $num1 + $num2;
            break;
        case '-':
            $result = $num1 - $num2;
            break;
        case '*':
            $result = $num1 * $num2;
            break;
        case '/':
            if ($num2 == 0) {
                $result = "Ошибка: деление на ноль!";
            } else {
                $result = $num1 / $num2;
            }
            break;
        default:
            $result = "Неверный оператор!";
    }

    echo "<h3>Результат: $result</h3>";

    // Сохранение истории в файл
    $historyFile = 'calculator_history.txt';
    $historyData = date('Y-m-d H:i:s') . " - $num1 $operator $num2 = $result\n";
    file_put_contents($historyFile, $historyData, FILE_APPEND);
}
?>
<h3>История операций</h3>
<?php
// Чтение истории из файла и отображение ее
if (file_exists('calculator_history.txt')) {
    $history = file_get_contents('calculator_history.txt');
    echo "<pre>$history</pre>";
} else {
    echo "История операций пуста.";
}
?>

</body>
</html>