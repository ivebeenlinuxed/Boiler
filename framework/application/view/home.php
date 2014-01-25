<div class="container">
<h1>Welcome to Boiler:Data</h1>
<p>Boiler:Data is the AJAX framework designed for data hackers. It's special because it does all the coding for you!</p>
<p>To begin do the following</p>
<ol>
	<li>Clone the repo: git clone https://github.com/ivebeenlinuxed/Boiler.git</li>
	<li>Build the config file: ant build</li>
	<li>Edit the config file config.php in your favourite editor</li>
	<li>Get Boiler to build all of your models for you: ant models</li>
	<li>Get Boiler to build all your API for you: ant api</li>
</ol>
<p>You're done!</p>
Below is a list of all the models that you have created. Make sure you run the API command for the links to work!

<table class="table table-striped table-bordered">
	<tr>
		<th colspan="4">Modal</th>
		<th colspan="4">Main</th>
	</tr>
	<tr>
		<th>List</th>
		<th>Add</th>
		<th>View</th>
		<th>Edit</th>
		<th>List</th>
		<th>Add</th>
		<th>View</th>
		<th>Edit</th>
	</tr>
<?php 
$dir = opendir(BOILER_LOCATION."/application/model/");
while ($d = readdir($dir)) {
	if ($d == "." || $d == "..") {
		continue;
	}
	if (!class_exists("\\Model\\".\System\Library\Lexical::getClassName(substr($d, 0, -4)))) {
		include BOILER_LOCATION."/application/model/$d";
	}
}
?>
<?php
foreach (get_declared_classes() as $class) {
	if (strpos($class, "Model\\") === 0) {
		if ($class == "Model\\DBObject") {
			continue;
		}
		echo "<tr><td><a data-type='api-modal' href='/{$class::getTable()}'>/{$class::getTable()}</a></td>";
		echo "<td><a data-type='api-modal' href='/{$class::getTable()}/add'>/{$class::getTable()}/add</a></td>";
		$data = $class::getAll(null, 0, 1);
		$key = $class::getPrimaryKey()[0];
		if (count($data) == 1 && is_object($data[0])) {
			$data = $data[0];
			echo "<td><a data-type='api-modal' href='/{$class::getTable()}/{$data->$key}'>{$class::getTable()}/{$data->$key}</a></td>";
			echo "<td><a data-type='api-modal' href='/{$class::getTable()}/{$data->$key}/edit'>{$class::getTable()}/{$data->$key}/edit</a></td>";
		} else {
			echo "<td>/{$class::getTable()}/{id}</td>";
			echo "<td>/{$class::getTable()}/{id}/edit</td>";
		}
		
		
		echo "<td><a href='/{$class::getTable()}'>/{$class::getTable()}</a></li>";
		echo "<td><a href='/{$class::getTable()}/add'>/{$class::getTable()}/add</a></li>";
		$data = $class::getAll(null, 0, 1);
		$key = $class::getPrimaryKey()[0];
		if (count($data) == 1 && is_object($data[0])) {
			$data = $data[0];
			echo "<td><a href='/{$class::getTable()}/{$data->$key}'>{$class::getTable()}/{$data->$key}</a></td>";
			echo "<td><a href='/{$class::getTable()}/{$data->$key}/edit'>{$class::getTable()}/{$data->$key}/edit</a></td>";
		} else {
			echo "<td>/{$class::getTable()}/{id}</td>";
			echo "<td>/{$class::getTable()}/{id}/edit</td>";
		}
		echo "</tr>";
	}
}
?>
</table>


<ul>

</ul>
</div>