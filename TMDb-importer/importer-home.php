<h2>VMA TMdB Importer</h2>

<div>

<h4>Import Movies Between Dates:</h4>
	<form method="GET" action="<?php echo admin_url( 'admin.php' ); ?>">
		<input type="hidden" name="f" value="import_from_tmdb">
		<input type="hidden" name="tmdb_import" value="1">
		<input type="text" name="vma_year" value="<?php global $vma_year; echo $vma_year; ?>">
		<input type="text" name="vma_startdate" placeholder="Start date">
		<input type="text" name="vma_enddate" placeholder="End date">
		<input type="text" name="vma_update" placeholder="Update data" value="0">
		<input type="text" name="popularity" placeholder="pop">
		<input type="submit" value="GO" class="button-primary">
	</form>
</div>


<div>

<h4>Import Movie by ID:</h4>
	<form method="GET" action="<?php echo admin_url( 'admin.php' ); ?>">
		<input type="hidden" name="tmdb_import" value="2">
		<input type="hidden" name="vma_update" value="1">
		<input type="text" name="movie_id" placeholder="TMdB id">
		<input type="submit" value="GO" class="button-primary">
	</form>
</div>


