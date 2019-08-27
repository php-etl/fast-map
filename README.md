```yaml
types:
  App\DTO\Customer:
    properties:
      prefix: {type: string }
      firstname: {type: string }
      middlename: {type: string }
      lastname: {type: string }
      suffix: {type: string }
    relations:
      primaryAddress:
        type: App\DTO\Address
      addresses:
        type: App\DTO\Address[]
  App\DTO\Address:
    properties:
      name: {type: string }
      street: {type: string }
      city: {type: string }  
      
mapping:
  import-customers-mapping:
    input:
      type: map
      properties:
        prefix: {type: string }
        firstname: {type: string }
        middlename: {type: string }
        lastname: {type: string }
        suffix: {type: string }
        addresses:
          type: list
          properties:
            street: {type: string }
            city: {type: string }  
    output:
      import: App\DTO\Customer

    apply:
      - '@Kiboko\Component\ETL\FastMap\FieldCopyValueMapper': 
          $inputField: '[prefix]'
          $outputField: 'prefix'
      - '@Kiboko\Component\ETL\FastMap\FieldCopyValueMapper': 
          $inputField: '[firstname]'
          $outputField: 'firstname'
      - '@Kiboko\Component\ETL\FastMap\FieldCopyValueMapper': 
          $inputField: '[middlename]'
          $outputField: 'middlename'
      - '@Kiboko\Component\ETL\FastMap\FieldCopyValueMapper': 
          $inputField: '[lastname]'
          $outputField: 'lastname'
      - '@Kiboko\Component\ETL\FastMap\FieldCopyValueMapper': 
          $inputField: '[suffix]'
          $outputField: 'suffix'
      - '@Kiboko\Component\ETL\FastMap\CollectionValueMapper': 
          $inputField: '[addresses]'
          $outputField: 'addresses'
          apply:
            - '@Kiboko\Component\ETL\FastMap\FieldCopyValueMapper': 
                $inputField: '[street]'
                $outputField: 'street'
            - '@Kiboko\Component\ETL\FastMap\FieldCopyValueMapper': 
                $inputField: '[city]'
                $outputField: 'city'
            - '@Kiboko\Component\ETL\FastMap\FieldConstantValueMapper': 
                $outputField: '[label]'
                $value: 'Lorem ipsum dolor sit amet'
            - '@Kiboko\Component\ETL\FastMap\FieldConcatCopyValuesMapper': 
                outputField: '[name]'
                $context: parent
                glue: ' '
                inputFields: [ '[prefix]', '[firstname]', '[middlename]', '[lastname]', '[suffix]' ]

```