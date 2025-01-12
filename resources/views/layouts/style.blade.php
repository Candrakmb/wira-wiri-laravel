<style>
    .card-plus {
        width: 100%;
        height: auto;
        border-radius: 30px;
        background: #ebebeb;
        box-shadow: 15px 15px 30px #bebebe,
            -15px -15px 30px #ffffff;
    }

    .button-primary {
        margin-top: 20px;
        margin-bottom: 20px;
        color: #4181f098;
        text-transform: uppercase;
        letter-spacing: 5px;
        /* border: none; */
        font-weight: bold;
        font-size: 15px;
        padding: 1rem 2rem;
        border: 1px solid #ffffff1f;
        border-radius: 20px;
        background: #ebebeb;
        -webkit-box-shadow: 5px 5px 15px #cccccc,
            -5px -5px 15px #ffffff;
        box-shadow: 5px 5px 15px #cccccc,
            -5px -5px 15px #ffffff;
        -webkit-transition: box-shadow 0.3s ease-in-out;
        transition: box-shadow 0.3s ease-in-out;
    }

    .button-primary:hover {
        color: #357af0;
        background: linear-gradient(145deg, #d4d4d4, #fbfbfb);
        -webkit-box-shadow: 5px 5px 15px #cccccc,
            -5px -5px 15px #ffffff;
        box-shadow: 5px 5px 15px #cccccc,
            -5px -5px 15px #ffffff;
    }

    .button-primary:active {
        color: #357af0;
        background: #ebebeb;
        -webkit-box-shadow: inset 5px 5px 15px #cccccc,
            inset -5px -5px 15px #ffffff;
        box-shadow: inset 5px 5px 15px #cccccc,
            inset -5px -5px 15px #ffffff;

    }

    .input {
        border: none;
        outline: none;
        border-radius: 100px;
        padding: 12px;
        padding-left: 20px;
        background-color: #e1e2e3;
        box-shadow: inset 2px 5px 10px rgba(0, 0, 0, 0.3);
        transition: 300ms ease-in-out;
    }

    .input:focus {
        background-color: #ffffff;
        transform: scale(1.05);
        box-shadow: 13px 13px 100px #969696, -13px -13px 100px #ffffff;
    }

    .select {
        border: none;
        outline: none;
        border-radius: 100px;
        padding: 12px;
        padding-left: 20px;
        background-color: #e1e2e3;
        box-shadow: inset 2px 5px 10px rgba(0, 0, 0, 0.3);
        transition: 300ms ease-in-out;
    }

    .select:focus {
        background-color: #ffffff;
        transform: scale(1.05);
        box-shadow: 13px 13px 100px #969696, -13px -13px 100px #ffffff;
    }

    .required {
        color: red;
    }

    .button_trash {
        width: 50px;
        height: 50px;
        border-radius: 20%;
        background-color: rgb(20, 20, 20);
        border: none;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.164);
        cursor: pointer;
        transition-duration: .3s;
        overflow: hidden;
        position: relative;
    }

    .svgIcon {
        width: 12px;
        transition-duration: .3s;
    }

    .svgIcon path {
        fill: white;
    }

    .button_trash:hover {
        width: 140px;
        border-radius: 50px;
        transition-duration: .3s;
        background-color: rgb(255, 69, 69);
        align-items: center;
    }

    .button_trash:hover .svgIcon {
        width: 50px;
        transition-duration: .3s;
        transform: translateY(60%);
    }

    .button_trash::before {
        position: absolute;
        top: -20px;
        content: "Delete";
        color: white;
        transition-duration: .3s;
        font-size: 2px;
    }

    .button_trash:hover::before {
        font-size: 13px;
        opacity: 1;
        transform: translateY(30px);
        transition-duration: .3s;
    }

    .wrapper-radio {
        --font-color-dark: #323232;
        --font-color-light: #FFF;
        --bg-color: #fff;
        --main-color: #323232;
        position: relative;
        width: 170px;
        height: 36px;
        background-color: var(--bg-color);
        border: 2px solid var(--main-color);
        border-radius: 34px;
        display: flex;
        flex-direction: row;
        box-shadow: 4px 4px var(--main-color);
    }

    .option-radio {
        width: 80.5px;
        height: 28px;
        position: relative;
        top: 2px;
        left: 2px;
    }

    .input-radio {
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
        appearance: none;
        cursor: pointer;
    }

    .btn-radio {
        width: 100%;
        height: 100%;
        background-color: var(--bg-color);
        border-radius: 50px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .span-radio {
        color: var(--font-color-dark);
    }

    .input-radio:checked+.btn-radio {
        background-color: var(--main-color);
    }

    .input-radio:checked+.btn-radio .span-radio {
        color: var(--font-color-light);
    }

    .container_file {
        height: 300px;
        width: 300px;
        border-radius: 10px;
        box-shadow: 4px 4px 30px rgba(0, 0, 0, .2);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
        gap: 5px;
        background-color: rgba(0, 110, 255, 0.041);
    }

    .header_file {
        flex: 1;
        width: 100%;
        border: 2px dashed royalblue;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    .header_file svg {
        height: 100px;
    }

    .header_file img {
        max-width: 230px;
        max-height: 230px;
        width: auto;
        height: auto;
    }


    .header_file p {
        text-align: center;
        color: black;
    }

    .footer_file {
        background-color: rgba(0, 110, 255, 0.075);
        width: 100%;
        height: 40px;
        padding: 8px;
        border-radius: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        color: black;
        border: none;
    }

    .footer_file svg {
        height: 130%;
        fill: royalblue;
        background-color: rgba(70, 66, 66, 0.103);
        border-radius: 50%;
        padding: 2px;
        cursor: pointer;
        box-shadow: 0 2px 30px rgba(0, 0, 0, 0.205);
    }

    .footer_file p {
        flex: 1;
        margin-top: 15px;
        text-align: center;
    }

    #file[] {
        display: none;
    }

    .button__pilihan {
        --main-focus: #2d8cf0;
        --font-color: #323232;
        --bg-color-sub: #dedede;
        --bg-color: #eee;
        --main-color: #323232;
        position: relative;
        width: 200px;
        height: 40px;
        cursor: pointer;
        display: flex;
        align-items: center;
        border: 2px solid var(--main-color);
        box-shadow: 4px 4px var(--main-color);
        background-color: var(--bg-color);
        border-radius: 10px;
        overflow: hidden;
    }

    .button__pilihan,
    .button__icon,
    .button__text {
        transition: all 0.3s;
    }

    .button__pilihan .button__text {
        transform: translateX(22px);
        color: var(--font-color);
        font-weight: 600;
    }

    .button__pilihan .button__icon {
        position: absolute;
        transform: translateX(150px);
        height: 100%;
        width: 39px;
        background-color: var(--bg-color-sub);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .button__pilihan .svg__pilihan {
        width: 20px;
        fill: var(--main-color);
    }

    .button__pilihan:hover {
        background: var(--bg-color);
    }

    .button__pilihan:hover .button__text {
        color: transparent;
    }

    .button__pilihan:hover .button__icon {
        width: 198px;
        transform: translateX(0);
    }

    .button__pilihan:active {
        transform: translate(3px, 3px);
        box-shadow: 0px 0px var(--main-color);
    }


    .button_back {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: rgb(20, 20, 20);
  border: none;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0px 0px 0px 4px rgba(180, 160, 255, 0.253);
  cursor: pointer;
  transition-duration: 0.3s;
  overflow: hidden;
  position: relative;
}

.svgIcon_back {
  width: 12px;
  transition-duration: 0.3s;
}

/* .svgIcon_back path {
  fill: white;
} */

.button_back:hover {
  width: 100px;
  border-radius: 50px;
  transition-duration: 0.3s;
  background-color: rgb(181, 160, 255);
  align-items: center;
}

.button_back:hover .svgIcon_back {
  /* width: 20px; */
  transition-duration: 0.3s;
  transform: translateY(-200%);
}

.button_back::before {
  position: absolute;
  bottom: -20px;
  content: "Kembali";
  color: white;
  /* transition-duration: .3s; */
  font-size: 0px;
}

.button_back:hover::before {
  font-size: 13px;
  opacity: 1;
  bottom: unset;
  /* transform: translateY(-30px); */
  transition-duration: 0.3s;
}
/* profile */
.card_profile {
  width: 210px;
  height: 280px;
  background: rgb(39, 39, 39);
  border-radius: 12px;
  box-shadow: 0px 0px 30px rgba(0, 0, 0, 0.123);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  transition-duration: .5s;
}

.profileImage {
  background: linear-gradient(to right,rgb(54, 54, 54),rgb(32, 32, 32));
  margin-top: 20px;
  width: 170px;
  height: 170px;
  border-radius: 50%;
  box-shadow: 5px 10px 20px rgba(0, 0, 0, 0.329);
}

.textContainer_profile {
  width: 100%;
  text-align: left;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.textContainer_profile .name_profile {
  font-size: 0.9em;
  font-weight: 600;
  color: white;
  letter-spacing: 0.5px;
}

.textContainer_profile .profile {
  font-size: 0.84em;
  color: rgb(194, 194, 194);
  letter-spacing: 0.2px;
}

.card_profile:hover {
  background-color: rgb(43, 43, 43);
  transition-duration: .5s;
}
/*style phone*/
.card_phone {
  width: 301px;
  height: 610px;
  background: black;
  border-radius: 35px;
  border: 2px solid rgb(40, 40, 40);
  padding: 7px;
  position: relative;
  box-shadow: 2px 5px 15px rgba(0, 0, 0, 0.486);
}

.card-int {
  background:white;
  background-size: 200% 200%;
  background-position: 0% 0%;
  height: 100%;
  border-radius: 25px;
  transition: all 0.6s ease-out;
  overflow: hidden;
}

.card_phone:hover .card-int {
  background-position: 100% 100%;
}

.top {
  position: absolute;
  z-index: 400;
  top: 0px;
  right: 50%;
  transform: translate(50%, 0%);
  width: 35%;
  height: 18px;
  background-color: black;
  border-bottom-left-radius: 10px;
  border-bottom-right-radius: 10px;
}

.speaker {
  position: absolute;

  top: 2px;
  right: 50%;
  transform: translate(50%, 0%);
  width: 40%;
  height: 2px;
  border-radius: 2px;
  background-color: rgb(20, 20, 20);
}

.camera {
  position: absolute;
  top: 6px;
  right: 84%;
  transform: translate(50%, 0%);
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.048);
}

.int {
  position: absolute;
  width: 3px;
  height: 3px;
  border-radius: 50%;
  top: 50%;
  right: 50%;
  transform: translate(50%, -50%);
  background-color: rgba(0, 0, 255, 0.212);
}

.btn1, .btn2, .btn3, .btn4 {
  position: absolute;
  width: 2px;
}

.btn1, .btn2, .btn3 {
  height: 45px;
  top: 30%;
  right: -4px;
  background-image: linear-gradient(to right, #111111, #222222, #333333, #464646, #595959);
}

.btn2, .btn3 {
  transform: scale(-1);
  left: -4px;
}

.btn2, .btn3 {
  transform: scale(-1);
  height: 30px;
}

.btn2 {
  top: 26%
}

.btn3 {
  top: 36%
}


.hidden {
  display: block;
  opacity: 0;
  transition: all 0.3s ease-in;
}

.card_phone:hover .hidden {
  opacity: 1;
}

.card_phone:hover .hello {
  transform: translateY(-20px);
}

/* card proses */
.card-proses{
    position: absolute;
    top: 45%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 400;
    padding: 12px;
    width: 90%;
    display: flex;
    align-items: center;
    height: 42px;
    border-radius: 10px;
    background: #ffffff;
    box-shadow: 1px 2px 10px 0px #555353;
}
.proses__icon {
  width: 30px;
  height: 30px;
  transform: translateY(-2px);
  margin-right: 17px
}
.proses__icon img{
  width: 100%;
}


.proses__title {
  font-weight: 500;
  font-size: 14px;
  color: #000000;
}
/* card detail */
.card-detail{
    position: absolute;
    top: 50%;
    z-index: 1000;
    padding: 12px;
    width: 95%;
    display: flex;
    align-items: center;
    height: 48%;
    border-radius: 30px;
    background: #f3f3f3;
    overflow: hidden
}
/* card driver */
</style>
