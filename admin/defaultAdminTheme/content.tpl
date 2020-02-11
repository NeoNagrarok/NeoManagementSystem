<h2>
	[?getContentTitle]
</h2>
[?listContentInner]
<form class="addContentField" method="post" action="./">
	<label for="addField">Add field : 
		<select id="addField" name="addField">
			<optgroup label="Text fields">
				<option value="text">Text</option>
				<option value="textarea">Textarea</option>
				<option value="email">Email</option>
				<option value="number">Number</option>
				<option value="url">URL</option>
				<option value="phone">Phone</option>
				<option value="date">Date</option>
			</optgroup>
			<optgroup label="Choice">
				<option value="radio">Unique</option>
				<option value="checkbox">Multiple</option>
				<option value="color">Color</option>
			</optgroup>
		</select>
	</label>
	<button type="submit">Add</button>
</form>
[?lastFieldAdded]
