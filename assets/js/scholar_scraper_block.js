//import icon from '../img/google-scholar';

// Get the icon content from ../img/google-scholar.svg as base64
/*const icon = new Image();
icon.src = 'data:image/svg+xml;base64,' + btoa( unescape( encodeURIComponent( "../img/google-scholar.svg" ) ) );

// On ajoute l'image à la fin du body
document.body.appendChild( icon );
console.log( icon);*/

// Create an element that can be user as an icon and loads the svg file
const icon = wp.element.createElement(
    'img',
    {
        src: js_data.image_data,
    }
);


wp.blocks.registerBlockType('scholar-scraper/scholar-scraper-block', {
    title: 'Scholar Scraper',
    icon: icon,
    category: 'widgets',
    attributes: {
        number_papers_to_show: {
            type: 'number',
            default: js_data.default_number_papers_to_show
        },
        sort_by_field: {
            type: 'string',
            default: js_data.default_sort_by_field
        },
        sort_by_direction: {
            type: 'string',
            default: js_data.default_sort_by_direction
        }
    },
    edit: function (props) {
        function updateNumberPaperToShow(event) {
            props.setAttributes({number_papers_to_show: Number(event.target.value)});
        }

        function updateSortByField(event) {
            props.setAttributes({sort_by_field: event.target.value});
        }

        function updateSortByDirection(event) {
            props.setAttributes({sort_by_direction: event.target.value});
        }


        return wp.element.createElement(
            'div',
            {className: props.className},
            wp.element.createElement(
                'div',
                {id: 'scholar-scraper-block-hero'},
                wp.element.createElement(
                    'div',
                    {id: 'scholar-scraper-block-icon'},
                    icon,
                ),
                wp.element.createElement(
                    'h4',
                    {id: 'scholar-scraper-block-title'},
                    'Scholar Scraper'
                ),
            ),
            wp.element.createElement(
                'div',
                {id: 'scholar-scraper-block-attributes'},
                wp.element.createElement(
                    'label',
                    {
                        for: "num_articles"
                    },
                    'Number of papers to show:'
                ),
                wp.element.createElement(
                    'input',
                    {
                        name: "num_articles",
                        type: 'number',
                        min: 1,
                        value: props.attributes.number_papers_to_show,
                        onChange: updateNumberPaperToShow
                    }
                ),
                wp.element.createElement(
                    'label',
                    {
                        for: "sort-by",

                    },
                    'Sort by:'
                ),
                wp.element.createElement(
                    'div',
                    {
                        id: "sort-by-container"
                    },
                    wp.element.createElement(
                        'select',
                        {
                            name: "sort-by-field",
                            id: "sort-by-field",
                            onChange: updateSortByField
                        },
                        // On crée une option pour chaque valeur du tableau js_data.available_sort_by
                        // La clé du tableau est la valeur de l'option et la valeur du tableau est le texte affiché
                        Object.keys(js_data.available_sort_by_fields).map(function (key) {
                            return wp.element.createElement(
                                'option',
                                {
                                    value: key,
                                    selected: key === props.attributes.sort_by_field
                                },
                                js_data.available_sort_by_fields[key]
                            )
                        }),
                    ),
                    wp.element.createElement(
                        'select',
                        {
                            name: "sort-by-direction",
                            id: "sort-by-direction",
                            onChange: updateSortByDirection
                        },
                        wp.element.createElement(
                            'option',
                            {
                                value: "asc",
                                selected: "asc" === props.attributes.sort_by_direction
                            },
                            'Ascending'
                        ),
                        wp.element.createElement(
                            'option',
                            {
                                value: "desc",
                                selected: "desc" === props.attributes.sort_by_direction
                            },
                            'Descending'
                        ),
                    ),
                ),
            )
        );
    },
    save: function (props) {
        return null;
    }
})
