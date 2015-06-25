<?xml version="1.0" encoding="US-ASCII"?>
<my:my:RedboxCollection xmlns:my="http://schemas.microsoft.com/office/infopath/2003/myXSD/2011-09-26T07:17:47"
                     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                     xmlns:xd="http://schemas.microsoft.com/office/infopath/2003">
<my:crate>
    <my:name><?php p($_['crate_name']) ?></my:name>
    <my:date><?php p($_['created_date']) ?></my:date>
    <my:location><?php print_unescaped($_['location']) ?></my:location>
    <my:description><?php p($_['description']) ?></my:description>
    <?php if ($_['creators']) { ?>
        <my:creators>
            <?php foreach ($_['creators'] as $creator) { ?>
                <my:creator>
                    <?php
                    print_unescaped('<my:name>');
                    p($creator['name']);
                    print_unescaped('</my:name>');
                    print_unescaped('<my:email>');
                    p($creator['email']);
                    print_unescaped('</my:email>');
                    ?>
                </my:creator>
            <?php } ?>
        </my:creators>
    <?php } ?>
    <my:submitter>
        <my:email><?php p($_['submitter']) ?></my:email>
    </my:submitter>
</my:crate>
