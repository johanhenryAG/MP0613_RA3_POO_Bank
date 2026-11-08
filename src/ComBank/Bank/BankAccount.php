<?php namespace ComBank\Bank;
 

/**
 * Created by VS Code.
 * User: JPortugal
 * Date: 7/27/24
 * Time: 7:25 PM
 */

use ComBank\Exceptions\BankAccountException;
use ComBank\Exceptions\InvalidArgsException;
use ComBank\Exceptions\ZeroAmountException;
use ComBank\OverdraftStrategy\NoOverdraft;
use ComBank\Bank\Contracts\BankAccountInterface;
use ComBank\Exceptions\FailedTransactionException;
use ComBank\Exceptions\InvalidOverdraftFundsException;
use ComBank\OverdraftStrategy\Contracts\OverdraftInterface;
use ComBank\Support\Traits\AmountValidationTrait;
use ComBank\Transactions\Contracts\BankTransactionInterface;

class BankAccount implements BankAccountInterface
{
    //atributos
    private float $balance;
    private bool $status;
    private OverdraftInterface $overdraft;

    private string $email = '';

    



    //constructor
    public function __construct(float $balance)
    {
        $this->balance = $balance;
        $this->status = true;
        $this->overdraft = new NoOverdraft();

    }
    //getBalance();
    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): void
    {
        $this->balance = $balance;
    }
    
    public function closeAccount(): void
    {
        $this->status = false;
    }

    public function reopenAccount(): void
    {
        if ($this->isOpen()) {
            throw new BankAccountException('Account is already open');
        }

        $this->status = true;
    } 

    public function isOpen(): bool
    {
    return $this->status;
    }

    public function getOverdraft(): OverdraftInterface
    {
        return $this->overdraft;
    }

    public function applyOverdraft(OverdraftInterface $overdraft): void
    {
        $this->overdraft = $overdraft;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }



    public function transaction(BankTransactionInterface $bankTransaction): void
    {
        if (!$this->isOpen()) {
            throw new BankAccountException('Account is closed');
        }

        try {
            $newBalance = $bankTransaction->applyTransaction($this);
            $this->setBalance($newBalance);
        } catch (InvalidOverdraftFundsException $e) {
            throw new FailedTransactionException($e->getMessage());
        }
    }     
}
