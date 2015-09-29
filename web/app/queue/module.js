define( {
  subject: 'queue',
  identifier: { column: 'name' },
  name: {
    singular: 'queue',
    plural: 'queues',
    possessive: 'queue\'s',
    pluralPossessive: 'queues\''
  },
  inputList: {
    rank: {
      title: 'Rank',
      type: 'rank',
      constant: true
    },
    name: {
      title: 'Name',
      type: 'string',
      constant: true
    },
    title: {
      title: 'Title',
      type: 'string',
      constant: true
    },
    description: {
      title: 'Description',
      type: 'text',
      constant: true
    }
  },
  columnList: {
    rank: {
      title: 'Rank',
      type: 'rank'
    },
    name: { title: 'Name' },
    participant_count: {
      title: 'Participants',
      type: 'number'
    }
  },
  defaultOrder: {
    column: 'rank',
    reverse: false
  }
} );