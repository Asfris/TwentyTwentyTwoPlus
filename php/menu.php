<?php

global $db;

// Check if default setting set in database
$result =  $db->get("SELECT * FROM wp_tttp;");

if (!$result)
{
    // that means table not filled by default data
    foreach (DB_TABLE_PARAMS as $param)
    {
        $db->insert(array("param" => $param, "value" => ""), array("%s", "%s"));
    }
}

/**
 * Set OR replace checkbox value to db
 * @since 0.1.0
 * @return void
 */
function set_checkbox(string $param)
{
    global $db;

    $data = $db->get("SELECT value FROM wp_tttp WHERE param='$param';");
    // If data exists
    if ($data)
    {
        $value = isset($_POST["$param"]);
        $db->update(array("value" => $value), array("param" => $param));
    }
}

/**
 * Get checkbox value from db
 * @since 0.1.0
 * @return string
 */
function get_checkbox(string $param): string
{
    global $db;

    $result = $db->get("SELECT value FROM wp_tttp WHERE param='$param';");

    if ($result)
    {
        return match ($result[0]->value)
        {
            "1"  => 'checked',
            "" => '',
        };
    }
    else
    {
        return '';
    }
}

global $update;
// Check update
[$ver, $check] = $update->check_update();
if ($check) echo "<div class='message'>New update is available -> $ver</div>";

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    if (isset($_POST['update']))
    {
        if ($check) $update->upgrade();
        else echo "<div class='message'>You are using latest version of plugin.</div>";
    }
    else
    {
        foreach (DB_TABLE_PARAMS as $param)
        {
            set_checkbox($param);
        }
    }
}

/**
 * Example Result : array(3) { [0]=> string(0) "" [1]=> string(7) "checked" [2]=> string(0) "" }
 */
$result_params_data = array();

foreach (DB_TABLE_PARAMS as $param)
{
    $data = get_checkbox($param);
    array_push($result_params_data, $data);
}

?>

<html lang="en">
<style>
    main form {
        display: flex;
        flex-direction: column;
        align-items: baseline;
        margin-top: 1rem;
    }

    input[type=submit] {
        background: none;
        border: 1px solid #a0a0a0;
        padding: 6px;
        cursor: pointer;
    }

    table {
        border: solid 1px #a0a0a0;
        background: #fff;
    }

    table,
    td {
        text-align: center;
        width: 20rem;
    }

    th {
        border-bottom: solid 1px #a0a0a0;
    }

    .tttp-bottom-section {
        position: fixed;
        bottom: 3rem;
    }

    hr {
        margin-bottom: 2rem;
        margin-top: 2rem;
    }

    .message {
        padding: 10px;
        text-align: center;
        box-shadow: 0 0 5px #7d7d7d7d;
    }
</style>

<main>
    <h1>تنظیمات افزونه</h1>

    <form method="POST" action="">
        <table>
            <thead>
                <tr>
                    <th>پارامتر</th>
                    <th>مقدار</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        فهرست دوم
                    </td>
                    <td>
                        <input type="checkbox" name="checkSecondMenu" value="checked" <?= $result_params_data[0] ?>>
                    </td>
                </tr>
                <tr>
                    <td>
                        حالت تاریک
                    </td>
                    <td>
                        <input type="checkbox" name="darkMode" value="checked" <?= $result_params_data[1] ?>>
                    </td>
                </tr>
                <tr>
                    <td>
                        تصویر شاخص نوشته ها
                    </td>
                    <td>
                        <input type="checkbox" name="postImage" value="checked" <?= $result_params_data[2] ?>>
                    </td>
                </tr>
            </tbody>
        </table>
        <input type="submit" value="ثبت تغییرات" class="button" style="margin-top: 1rem;">
    </form>
</main>
<hr>
<div class="tttp-bottom-section">
    <p>نسخه : <?php echo get_plugin_version(); ?></p>
    <form method="POST" action="">
        <input type="submit" value="بروزرسانی" class="button" name="update">
    </form>
</div>

</html>