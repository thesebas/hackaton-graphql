# what is graphql

- replacement / alternative for REST/soap
- more expresive 
- orient

# examples


## artist (by name) -> albums (with limit)

        {
          artist(name: "Slipknot") {
            name
            url
            albums(limit: 5) {
              name
            }
          }
        }


## recursive similar artists

        {
          artist(name: "Slipknot") {
            name
            similar {
              name
              similar {
                name
              }
            }
          }
        }


## find artists (by pattern) -> show their albums

        {
          artistsearch(pattern: "stone", limit: 3) {
            name
            url
            similar {
              name
              url
              albums(limit: 3) {
                name
              }
            }
          }
        }



