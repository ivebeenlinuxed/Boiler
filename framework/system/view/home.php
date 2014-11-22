<?php 
\Core\Router::loadView("api/html/_template/".\Core\Router::$disposition."/top");
?>
<h1>Welcome to Boiler:Data</h1>
<p>Boiler:Data is the AJAX framework designed for data hackers. It's special because it does all the coding for you!</p>
<p>Also, because it's built on Boiler, it's fast, easy to manipulate and easy to extend!</p>
<p>To begin just follow these simple steps</p>
<ol>
	<li>Clone the repo: git clone https://github.com/ivebeenlinuxed/Boiler.git</li>
	<li>Point your webserver to [project_root]/htdocs (make sure .htaccess rewrite is on)</li>
	<li>Build the config file: ant build (you may need to install 'ant')</li>
	<li>Edit the config file config.php in your favourite editor</li>
	<li>Get Boiler to build all of your models for you: ant models</li>
	<li>Get Boiler to build all your API for you: ant api</li>
</ol>
<p>You're done!</p>
Below is a list of all the models that you have created. Make sure you run "ant api" command, as above, for the links to work!

<table class="table table-striped table-bordered">
	<tr>
		<th colspan="4">Modal</th>
	</tr>
	<tr>
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
		echo "<tr><td><a data-type='api-modal' href='/api/{$class::getTable()}'>/api/{$class::getTable()}</a></td>";
		echo "<td><a data-type='api-modal' href='/api/{$class::getTable()}/add'>/api/{$class::getTable()}/add</a></td>";
		$data = $class::getAll(null, 0, 1);
		$key = $class::getPrimaryKey()[0];
		if (count($data) == 1 && is_object($data[0])) {
			$data = $data[0];
			echo "<td><a data-type='api-modal' href='/api/{$class::getTable()}/{$data->$key}'>/api/{$class::getTable()}/{$data->$key}</a></td>";
			echo "<td><a data-type='api-modal' href='/api/{$class::getTable()}/{$data->$key}/edit'>/api/{$class::getTable()}/{$data->$key}/edit</a></td>";
		} else {
			echo "<td>/api/{$class::getTable()}/{id}</td>";
			echo "<td>/api/{$class::getTable()}/{id}/edit</td>";
		}
	}
}
?>
</table>
<table class="table table-striped table-bordered">
	<tr>
		<th colspan="4">Main</th>
	</tr>
	<tr>
		<th>List</th>
		<th>Add</th>
		<th>View</th>
		<th>Edit</th>
	</tr>
<?php
foreach (get_declared_classes() as $class) {
	if (strpos($class, "Model\\") === 0) {
		if ($class == "Model\\DBObject") {
			continue;
		}
		
		echo "<td><a href='/api/{$class::getTable()}'>/api/{$class::getTable()}</a></td>";
		echo "<td><a href='/api/{$class::getTable()}/add'>/api/{$class::getTable()}/add</a></td>";
		$data = $class::getAll(null, 0, 1);
		$key = $class::getPrimaryKey()[0];
		if (count($data) == 1 && is_object($data[0])) {
			$data = $data[0];
			echo "<td><a href='/api/{$class::getTable()}/{$data->$key}'>/api/{$class::getTable()}/{$data->$key}</a></td>";
			echo "<td><a href='/api/{$class::getTable()}/{$data->$key}/edit'>/api/{$class::getTable()}/{$data->$key}/edit</a></td>";
		} else {
			echo "<td>/api/{$class::getTable()}/{id}</td>";
			echo "<td>/api/{$class::getTable()}/{id}/edit</td>";
		}
		echo "</tr>";
	}
}
?>
</table>
<table class="table table-striped table-bordered">
	<tr>
		<th colspan="4">JSON</th>
	</tr>
	<tr>
		<th>List</th>
		<th>View</th>
	</tr>
<?php
foreach (get_declared_classes() as $class) {
	if (strpos($class, "Model\\") === 0) {
		if ($class == "Model\\DBObject") {
			continue;
		}
		
		echo "<td><a href='/api/{$class::getTable()}.json'>/api/{$class::getTable()}.json</a></td>";
		$data = $class::getAll(null, 0, 1);
		$key = $class::getPrimaryKey()[0];
		if (count($data) == 1 && is_object($data[0])) {
			$data = $data[0];
			echo "<td><a href='/api/{$class::getTable()}/{$data->$key}.json'>/api/{$class::getTable()}/{$data->$key}.json</a></td>";
		} else {
			echo "<td>/api/{$class::getTable()}/{id}.json</td>";
		}
		echo "</tr>";
	}
}
?>
</table>
<table class="table table-striped table-bordered">
	<tr>
		<th colspan="2">Special Functions</th>
	</tr>
	<tr>
		<th>Filter</th>
<?php
foreach (get_declared_classes() as $class) {
	if (strpos($class, "Model\\") === 0) {
		if ($class == "Model\\DBObject") {
			continue;
		}
		
		echo "<td><a href='/api/{$class::getTable()}.html?__where=%5B%5B%22id%22%2C%22!%3D%22%2C%221%22%5D%5D'>/api/{$class::getTable()}?__where=[['id', '!=', 1]]</a></td>";
		break;
	}
}
?>
</tr>
	<tr>
		<th>Page (size 4, page 1 - pages start at zero)</th>
<?php
foreach (get_declared_classes() as $class) {
	if (strpos($class, "Model\\") === 0) {
		if ($class == "Model\\DBObject") {
			continue;
		}
		
		echo "<td><a href='/api/{$class::getTable()}.html?__X_PAGE=2/4'>/api/{$class::getTable()}?__X_PAGE=1/4</a></td>";
		break;
	}
}
?>
</tr>
<tr>
	<th>Headers: X-*</th>
	<td>?__X_* (Where headers cannot be sent nicely - such as an HTML anchor tag)</td>
</tr>
<tr>
	<th>Request Method (e.g. PUT,DELETE,GET,POST)</th>
	<td>X-Request-Method: PUT</td>
</tr>
<tr>
	<th>Order By</th>
	<td>X-Order-By: id+, name- (Not yet implemented)</td>
</tr>
<tr>
	<th>Show only fields id and name</th>
	<td>X-Fields: id, name (Not yet implemented)</td>
</tr>
</table>

<table class="table table-striped table-bordered">
	<tr>
		<th colspan="3">Widgets</th>
	</tr>
	<tr>
		<th>Text Widget</th>
		<td><?php $w = new \Controller\Widget\Text(); $w->RenderAnonymous() ?></td>
	</tr>
	<tr>
		<th>Select Widget</th>
		<td>
			<select is="select-widget">
				<option>Testing 1</option>
				<option>Testing 2</option>
			</select>
		</td>
	</tr>
</table>

<?php 
\Core\Router::loadView("api/html/_template/".\Core\Router::$disposition."/bottom");
?>
