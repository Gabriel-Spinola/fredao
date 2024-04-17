declare namespace FrontFredao {
    export type UserInfo = {
        username: string,
        password: string,
    }
    
    export type APIResponse = {
        status: number,
        message: string,
    }

    export type FrabricatedResponse = {} 
    export type ESResponse<DataType, CustomError = string | unknown> =
        | {
            data: DataType
            error: null
            }
        | {
            data: null
            error: CustomError
            }
}