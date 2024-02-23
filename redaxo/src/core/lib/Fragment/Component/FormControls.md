FormControls

| Property       | Type                            | Default                   | Textarea | Input                             | Choice                               | Checkbox |
|----------------|---------------------------------|---------------------------|----------|-----------------------------------|--------------------------------------|----------|
| label          | string, Fragment, null          | null                      | x        | x                                 | x                                    | x        |
| notice         | string, Fragment, null          | null                      | x        | x                                 | x                                    |          |
| prefix         | string, Fragment, null          | null                      |          | x                                 |                                      |          |
| suffix         | string, Fragment, null          | null                      |          | x                                 |                                      |          |
| type           | -->                             | -->                       |          | x `InputType` : `InputType::Text` | x `ChoiceType` : `ChoiceType:Select` |          |
| name           | string                          | null                      | x        | x                                 | x                                    | x        |
| value          | null, string, array             | null                      | x        | x                                 | x                                    | x        |
| disabled       | bool                            | false                     | x        | x                                 | x                                    | x        |
| placeholder    | string                          | null                      | x        | x                                 | x                                    |          |
| readonly       | bool                            | false                     | x        | x                                 |                                      |          | 
| required       | bool                            | false                     | x        | x                                 | x                                    | x        | 
| pattern        | string                          | null                      |          | x                                 |                                      |          | 
| minlength      | int                             | null                      | x        | x                                 |                                      |          | 
| maxlength      | int                             | null                      | x        | x                                 |                                      |          | 
| min            | null, int, string               | null                      |          | x                                 |                                      |          |
| max            | null, int, string               | null                      |          | x                                 |                                      |          |
| step           | null, int, float, string        | null                      |          | x                                 |                                      |          | 
| autofocus      | bool                            | false                     | x        | x                                 |                                      |          |
| attributes     | array                           | null                      | x        | x                                 | x                                    | x        |
| choices        | array                           | []                        |          |                                   | x                                    |          |
| multiple       | bool                            | false                     |          |                                   | x                                    |          |
| checked        | bool                            | false                     |          |                                   |                                      | x        |
| indeterminate  | bool                            | false                     |          |                                   |                                      | x        |
| rows           | int                             | 4                         | x        |                                   |                                      |          |
| resize         | TextareaResize                  | TextareaResize::Vertical  | x        |                                   |                                      |          |
| autocapitalize | null, Autocapitalize            | null                      | x        | x                                 |                                      |          |
| spellcheck     | bool                            | true                      | x        |                                   |                                      |          |


