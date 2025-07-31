<form action="search_jobs.php" method="GET" style="max-width:600px;margin:auto;margin-bottom:30px;">
    <input type="text" name="profession" placeholder="Професия" style="width:100%;padding:10px;margin-bottom:10px;">
    <input type="text" name="city" placeholder="Град" style="width:100%;padding:10px;margin-bottom:10px;">
    <select name="job_type" style="width:100%;padding:10px;margin-bottom:10px;">
        <option value="">Тип обява</option>
        <option value="offer">Предлагам работа</option>
        <option value="seek">Търся работа</option>
    </select>
    <input type="number" name="min_price" placeholder="Минимална цена" style="width:100%;padding:10px;margin-bottom:10px;">
    <input type="number" name="max_price" placeholder="Максимална цена" style="width:100%;padding:10px;margin-bottom:10px;">
    <button type="submit" style="width:100%;padding:10px;background:green;color:white;border:none;">Филтрирай</button>
</form>