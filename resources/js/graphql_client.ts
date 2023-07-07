import {GraphQLClient} from "graphql-request";

export const GqlClient = new GraphQLClient('/gql')
GqlClient.setHeader('X-Requested-With', 'XMLHttpRequest')
GqlClient.setHeader('Accept', 'application/json')
