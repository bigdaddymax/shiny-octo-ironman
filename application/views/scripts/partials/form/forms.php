<div id="add-form" title="Apply for approval">
    <form id="app-form">
        <input type="text" id="formName" name="formName" placeholder="<?php echo $this->translate('invoice name');?>"/>
        <?php
        echo '<select id="expgroup" name="expgroup">' . PHP_EOL;
        foreach($this->expgroups as $group){
            echo '<option value="' . $group . '">' . $group . '</option>' . PHP_EOL;
        }
        echo '</select>' . PHP_EOL;
        echo '<select id="nodeId" name="nodeId">';
        foreach ($this->nodes as $node) {
            echo '<option value="' . $node->nodeId . '">' . $node->nodeName . '</option>';
        }
        echo '</select>';
        ?>
    </form>
    <div>
        <table width="100%">
            <tr id="items-table">
                <td></td>
                <td>Item</td>
                <td>Value</td>
            </tr>
        </table>
    </div>
    <button id="add-item-btn">Add item to application</button>
</div>

<div id="add-item" title="Add approval item">
    <form id="item-form">
        <table>
            <tr><td>
                    <?php
                    echo '<select id="elementId" name="elementId">';
                    foreach ($this->elements as $element) {
                        echo '<option value="' . $element->elementId . '">' . $element->elementName . '</option>';
                    }
                    echo '</select>';
                    ?>
                </td></tr>
            <tr><td>
                    <label for="itemName">Description</label>
                </td></tr>
            <tr><td>
                    <input type="text" id="itemName" name="itemName">
                </td></tr>
            <tr><td>
                    <label for="value">Value</label>
                </td></tr>
            <tr><td>
                    <input type="text" id="value" name="value">
                </td></tr>
        </table>
    </form>

</div>

<button id="add-form-btn">Add application</button>
<form id="items">
    <input type="hidden" id="counter" value="0">
</form>
