<crate>
    <name><?php p($_['crate_name']) ?></name>
    <date><?php p($_['created_date']) ?></date>
    <location><?php print_unescaped($_['location']) ?></location>
    <description><?php p($_['description']) ?></description>
    <?php if ($_['creators']) { ?>
        <creators>
            <?php foreach ($_['creators'] as $creator) { ?>
                <creator>
                    <?php
                    print_unescaped('<name>');
                    p($creator['name']);
                    print_unescaped('</name>');
                    print_unescaped('<email>');
                    p($creator['email']);
                    print_unescaped('</email>');
                    ?>
                </creator>
            <?php } ?>
        </creators>
    <?php } ?>
    <submitter>
        <email><?php p($_['submitter']) ?></email>
    </submitter>
</crate>