/* eslint-disable */
import { DateTime } from 'luxon';

/* Get the current instant */
const now = DateTime.now();

export const boards = [
    {
        id          : '2c82225f-2a6c-45d3-b18a-1132712a4234',
        title       : 'Admin Dashboard',
        description : 'Roadmap for the new project',
        icon        : 'heroicons_outline:template',
        lastActivity: now.startOf('day').minus({day: 1}).toISO(),
        members     : [
            '9c510cf3-460d-4a8c-b3be-bcc3db578c08',
            'baa88231-0ee6-4028-96d5-7f187e0f4cd5',
            '18bb18f3-ea7d-4465-8913-e8c9adf6f568'
        ]
    },
];
export const lists = [
    {
        id      : 'a2df7786-519c-485a-a85f-c09a61cc5f37',
        boardId : '2c82225f-2a6c-45d3-b18a-1132712a4234',
        position: 65536,
        title   : 'To do'
    },
];
export const cards = [
    {
        id         : 'e74e66e9-fe0f-441e-a8ce-28ed6eccc48d',
        boardId    : '2c82225f-2a6c-45d3-b18a-1132712a4234',
        listId     : 'a2df7786-519c-485a-a85f-c09a61cc5f37',
        position   : 65536,
        title      : 'Example that showcase all of the available bits on the card with a fairly long title compared to other cards',
        description: 'Example that showcase all of the available bits on the card with a fairly long title compared to other cards. Example that showcase all of the available bits on the card with a fairly long title compared to other cards.',
        labels     : [
            'e0175175-2784-48f1-a519-a1d2e397c9b3',
            '51779701-818a-4a53-bc16-137c3bd7a564',
            'e8364d69-9595-46ce-a0f9-ce428632a0ac',
            'caff9c9b-a198-4564-b1f4-8b3df1d345bb',
            'f9eeb436-13a3-4208-a239-0d555960a567'
        ],
        dueDate    : now.startOf('day').minus({day: 10}).toISO()
    },
 
];
export const labels = [
    {
        id     : 'e0175175-2784-48f1-a519-a1d2e397c9b3',
        boardId: '2c82225f-2a6c-45d3-b18a-1132712a4234',
        title  : 'Research'
    },
    {
        id     : '51779701-818a-4a53-bc16-137c3bd7a564',
        boardId: '2c82225f-2a6c-45d3-b18a-1132712a4234',
        title  : 'Wireframing'
    },
    {
        id     : 'e8364d69-9595-46ce-a0f9-ce428632a0ac',
        boardId: '2c82225f-2a6c-45d3-b18a-1132712a4234',
        title  : 'Design'
    },
    {
        id     : 'caff9c9b-a198-4564-b1f4-8b3df1d345bb',
        boardId: '2c82225f-2a6c-45d3-b18a-1132712a4234',
        title  : 'Development'
    },
    {
        id     : 'f9eeb436-13a3-4208-a239-0d555960a567',
        boardId: '2c82225f-2a6c-45d3-b18a-1132712a4234',
        title  : 'Bug'
    }
];
export const members = [
    {
        id    : '6f6a1c34-390b-4b2e-97c8-ff0e0d787839',
        name  : 'Angeline Vinson',
        avatar: 'assets/images/avatars/female-01.jpg'
    },
    {
        id    : '4ce4be48-c8c0-468d-9df8-ddfda14cdb37',
        name  : 'Roseann Greer',
        avatar: 'assets/images/avatars/female-02.jpg'
    },
    {
        id    : '9c510cf3-460d-4a8c-b3be-bcc3db578c08',
        name  : 'Lorraine Barnett',
        avatar: 'assets/images/avatars/female-03.jpg'
    },
    {
        id    : '7ec887d9-b01a-4057-b5dc-aaed18637cc1',
        name  : 'Middleton Bradford',
        avatar: 'assets/images/avatars/male-01.jpg'
    },
    {
        id    : '74975a82-addb-427b-9b43-4d2e03331b68',
        name  : 'Sue Hays',
        avatar: 'assets/images/avatars/female-04.jpg'
    },
    {
        id    : '18bb18f3-ea7d-4465-8913-e8c9adf6f568',
        name  : 'Keith Neal',
        avatar: 'assets/images/avatars/male-02.jpg'
    },
    {
        id    : 'baa88231-0ee6-4028-96d5-7f187e0f4cd5',
        name  : 'Wilkins Gilmore',
        avatar: 'assets/images/avatars/male-03.jpg'
    },
    {
        id    : '0d1eb062-13d5-4286-b8d4-e0bea15f3d56',
        name  : 'Baldwin Stein',
        avatar: 'assets/images/avatars/male-04.jpg'
    },
    {
        id    : '5bf7ed5b-8b04-46b7-b364-005958b7d82e',
        name  : 'Bobbie Cohen',
        avatar: 'assets/images/avatars/female-05.jpg'
    },
    {
        id    : '93b1a72b-e2db-4f77-82d6-272047433508',
        name  : 'Melody Peters',
        avatar: 'assets/images/avatars/female-06.jpg'
    },
    {
        id    : 'd1f612e6-3e3b-481f-a8a9-f917e243b06e',
        name  : 'Marquez Ryan',
        avatar: 'assets/images/avatars/male-05.jpg'
    },
    {
        id    : '79ebb9ee-1e57-4706-810c-03edaec8f56d',
        name  : 'Roberta Briggs',
        avatar: 'assets/images/avatars/female-07.jpg'
    },
    {
        id    : '6726643d-e8dc-42fa-83a6-b4ec06921a6b',
        name  : 'Robbie Buckley',
        avatar: 'assets/images/avatars/female-08.jpg'
    },
    {
        id    : '8af617d7-898e-4992-beda-d5ac1d7ceda4',
        name  : 'Garcia Whitney',
        avatar: 'assets/images/avatars/male-06.jpg'
    },
    {
        id    : 'bcff44c4-9943-4adc-9049-08b1d922a658',
        name  : 'Spencer Pate',
        avatar: 'assets/images/avatars/male-07.jpg'
    },
    {
        id    : '54160ca2-29c9-4475-88a1-31a9307ad913',
        name  : 'Monica Mcdaniel',
        avatar: 'assets/images/avatars/female-09.jpg'
    },
    {
        id    : '51286603-3a43-444e-9242-f51fe57d5363',
        name  : 'Mcmillan Durham',
        avatar: 'assets/images/avatars/male-08.jpg'
    },
    {
        id    : '319ecb5b-f99c-4ee4-81b2-3aeffd1d4735',
        name  : 'Jeoine Hebert',
        avatar: 'assets/images/avatars/female-10.jpg'
    },
    {
        id    : 'fe0fec0d-002b-406f-87ab-47eb87ba577c',
        name  : 'Susanna Kline',
        avatar: 'assets/images/avatars/female-11.jpg'
    },
    {
        id    : '23a47d2c-c6cb-40cc-af87-e946a9df5028',
        name  : 'Suzette Singleton',
        avatar: 'assets/images/avatars/female-12.jpg'
    }
];
