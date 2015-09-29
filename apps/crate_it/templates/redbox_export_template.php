<?xml version="1.0" encoding="US-ASCII"?>
<my:RedboxCollection xmlns:my="http://schemas.microsoft.com/office/infopath/2003/myXSD/2011-09-26T07:17:47"
                     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                     xmlns:xd="http://schemas.microsoft.com/office/infopath/2003">

    <my:Title><?php p($_['crate_name']) ?></my:Title>
    <my:ID><?php p($_['crate_name']) ?></my:ID>
    <my:DateCreated><?php p($_['created_date']) ?></my:DateCreated>
    <my:Description><?php p($_['description']) ?></my:Description>
    <my:URL><?php print_unescaped($_['location']) ?></my:URL>
    <my:Type>dataset</my:Type>
    <my:WorkflowSource>Owncloud-Cr8IT</my:WorkflowSource>
    <my:WorkflowSourceVersion><?php p($_['version']) ?></my:WorkflowSourceVersion>
    <?php if ($_['creators']) { ?>
        <my:Creators>
            <?php foreach ($_['creators'] as $creator) { ?>
                <my:Creator>
                    <my:CreatorName>
                        <?php print_unescaped($creator['name']) ?>
                    </my:CreatorName>
                    <my:CreatorEmail>
                        <?php print_unescaped($creator['email']) ?>
                    </my:CreatorEmail>
                </my:Creator>
            <?php } ?>
        </my:Creators>
    <?php } ?>
    <?php if ($_['activities']) { ?>
        <my:GrantNumbers>
            <?php foreach ($_['activities'] as $activity) { ?>
                <my:GrantNumber>
                    <my:GrantNumberID>
                        <?php print_unescaped($activity['grant_number']) ?>
                    </my:GrantNumberID>
                    <my:GrantNumberDescription>
                        <?php print_unescaped($activity['title']) ?>
                    </my:GrantNumberDescription>
                </my:GrantNumber>
            <?php } ?>
        </my:GrantNumbers>
    <?php } ?>
    <my:Submitter>
        <my:SubmitterEmail><?php p($_['submitter']) ?></my:SubmitterEmail>
    </my:Submitter>
    <my:Contents>
        <?php foreach ($_['files'] as $file) { ?>
            <my:File>
                <my:FileName>
                    <?php print_unescaped($file['name']) ?>
                </my:FileName>
                <my:FileSize>
                    <?php print_unescaped($file['size']) ?>
                </my:FileSize>
            </my:File>
        <?php } ?>
    </my:Contents>
</my:RedboxCollection>
