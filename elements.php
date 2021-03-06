<?php
$elementSetMetadata = array(
    'name' => 'Monitor',
    'description' => 'Metadata used to manage various status about items.',
    'record_type' => 'Item',
    'elements' => array(
        array(
            'name' => 'metadata-status',
            'label' => 'Metadata Status',
            'description' => 'Main status of metadata of the record, set by the staff member that created the document metadata/record.',
            'comment' => '',
            'unique' => true,
            'terms' => array(
                'Incomplete',
                'Complete',
                'Fact Checked',
                'Ready to Publish',
                'Published',
            ),
            'steppable' => true,
            'default' => '',
        ),
        array(
            'name' => 'transcription-status',
            'label' => 'Transcription Status',
            'description' => 'Status of the transcription, set by the staff member that did the initial transcription.',
            'comment' => '',
            'unique' => true,
            'terms' => array(
                'Pending',
                'Completed',
                'Proofread',
                'Ready to Publish',
                'Published',
                'Restricted',
            ),
            'steppable' => true,
            'default' => '',
        ),
        array(
            'name' => 'translation-status',
            'label' => 'Translation Status',
            'description' => 'Status of the translation, set by the person who did the translation.',
            'comment' => '',
            'unique' => true,
            'terms' => array(
                'Not Needed',
                'Pending',
                'Completed',
                'Proofread',
                'Ready to Publish',
                'Published',
                'Restricted',
            ),
            'steppable' => true,
            'default' => '',
        ),
        array(
            'name' => 'research-status',
            'label' => 'Research Status',
            'description' => 'Status of the research, set by the staff member that did the task.',
            'comment' => '',
            'unique' => true,
            'terms' => array(
                'Pending',
                'Completed',
                'Fact Checked',
                'Ready to Publish',
                'Published',
            ),
            'steppable' => true,
            'default' => '',
        ),
        array(
            'name' => 'facsimile-permission-status',
            'label' => 'Facsimile Permission Status',
            'description' => 'Status of the Facsimile Permission, set by the staff member that verified it.',
            'comment' => '',
            'unique' => true,
            'terms' => array(
                'Pending',
                'Cleared',
                'Restricted',
            ),
            'steppable' => true,
            'default' => '',
        ),
        array(
            'name' => 'copyright-transcription-status',
            'label' => 'Copyright Status for the Transcription',
            'description' => 'Status of the Copyright for the transcription, set by the staff member that checked it .',
            'comment' => '',
            'unique' => true,
            'terms' => array(
                'Pending',
                'Cleared',
                'Restricted',
            ),
            'steppable' => true,
            'default' => '',
        ),
        array(
            'name' => 'transcription-difficulty',
            'label' => 'Transcription Difficulty',
            'description' => 'Information on the difficulty of the transcription according to the document.',
            'comment' => '',
            'unique' => true,
            'terms' => array(
                'Easy',
                'Medium',
                'Difficult',
            ),
            'steppable' => false,
            'default' => '',
        ),
        array(
            'name' => 'editor-notes',
            'label' => 'Editor’s Notes',
            'description' => 'Editor’s notes on the document/record provide a place for textual and contextual notes.',
            'comment' => '',
        ),
        array(
            'name' => 'administrative-notes',
            'label' => 'Administrative Notes',
            'description' => 'Administrative Notes summarize work needed to be done for incomplete documents.',
            'comment' => '',
        ),
        array(
            'name' => 'publish-record',
            'label' => 'Publish Record',
            'description' => 'Publish record except if the field is set to "No".',
            'comment' => '',
            'unique' => true,
            'automatic' => true,
            'terms' => array(
                'Yes',
                'No',
            ),
            'steppable' => false,
            'default' => '',
        ),
        array(
            'name' => 'publish-images',
            'label' => 'Publish Images',
            'description' => 'Publish images except if the field is set to "No".',
            'comment' => '',
            'unique' => true,
            'automatic' => true,
            'terms' => array(
                'Yes',
                'No',
            ),
            'steppable' => false,
            'default' => '',
        ),
        array(
            'name' => 'publish-transcription',
            'label' => 'Publish Transcription',
            'description' => 'Publish the transcription except if the field is set to "No".',
            'comment' => '',
            'unique' => true,
            'automatic' => true,
            'terms' => array(
                'Yes',
                'No',
            ),
            'steppable' => false,
            'default' => '',
        ),
    ),
);
